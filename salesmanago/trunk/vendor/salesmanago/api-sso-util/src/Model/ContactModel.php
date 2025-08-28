<?php


namespace SALESmanago\Model;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Address;
use SALESmanago\Entity\Contact\Options;
use SALESmanago\Entity\Contact\Properties;
use SALESmanago\Entity\ApiDoubleOptIn;

use SALESmanago\Entity\Configuration;

use SALESmanago\Helper\DataHelper;
use SALESmanago\Model\Collections\ConsentsCollection;


class ContactModel
{
    /**
     * @var Contact
     */
    protected $Contact;

    /**
     * @var ConfigurationInterface
     */
    protected $conf;

    /**
     * ContactModel constructor.
     * @param Contact $Contact
     * @param ConfigurationInterface $conf
     */
    public function __construct(Contact $Contact, ConfigurationInterface $conf)
    {
        $this->Contact = $Contact;
        $this->conf = $conf;
    }

    /**
     * @return array
     */
    public function getContactForUnionTransfer()
    {
        return self::toArray($this->Contact, $this->conf);
    }

    /**
     * Create contact request part for contact basic method
     */
    public function getContactForBasicRequest()
    {
        return DataHelper::filterDataArray([
            Configuration::CLIENT_ID => $this->conf->getClientId(),
            Options::ASYNC => $this->Contact->getOptions()->getAsync(),
            Contact::EMAIL => [
                $this->Contact->getEmail()
            ],
            Configuration::OWNER => $this->conf->getOwner(),
        ]);
    }

    /**
     * Create Contact from Basic response;
     * @param array $response
     * @return Contact $Contact
     */
    public function getContactFromBasicResponse($response)
    {
        $this->Contact
            ->setName($response['name'])
            ->setEmail($response['email'])
            ->setPhone($response['phone'])
            ->setFax($response['fax'])
            ->setScore($response['score'])
            ->setState($response['state'])
            ->setCompany($response['company'])
            ->setExternalId($response['externalId'])
            ->setContactId($response['contactId'])
            ->setBirthdayYear($response['birthdayYear'])
            ->setBirthdayMonth($response['birthdayMonth'])
            ->setBirthdayDay($response['birthdayDay']);

        $this->Contact->getOptions()
            ->setOptedOut($response['optedOut'])
            ->setOptedOutPhone($response['optedOutPhone'])
            ->setDeleted($response['deleted'])
            ->setInvalid($response['invalid'])
            ->setModifiedOn($response['modifiedOn'])
            ->setCreatedOn($response['createdOn'])
            ->setLastVisit($response['lastVisit']);

        if(isset($response['address']) && is_array($response['address'])) {
            $this->Contact->getAddress()
                ->setStreetAddress($response['address']['streetAddress'])
                ->setZipCode($response['address']['zipCode'])
                ->setCity($response['address']['city'])
                ->setCountry($response['address']['country']);
        }

        return $this->Contact;
    }

    /**
     * @param Contact $Contact
     * @param ConfigurationInterface $conf
     * @return array
     */
    public static function toExportArray(Contact $Contact, ConfigurationInterface $conf) {
        $Address    = $Contact->getAddress();
        $Options    = $Contact->getOptions();
        $Properties = $Contact->getProperties();

        $contactRequestArray = [
            Contact::CONTACT    => [
                Contact::EMAIL   => $Contact->getEmail(),
                Contact::FAX     => $Contact->getFax(),
                Contact::NAME    => $Contact->getName(),
                Contact::PHONE   => $Contact->getPhone(),
                Contact::COMPANY => $Contact->getCompany(),
                Contact::EXT_ID  => $Contact->getExternalId(),
                Contact::STATE   => $Contact->getState(),
                Contact::ADDRESS => [
                    Address::STREET_AD => $Address->getStreetAddress(),
                    Address::ZIP_CODE  => $Address->getZipCode(),
                    Address::CITY      => $Address->getCity(),
                    Address::COUNTRY   => $Address->getCountry()
                ],
            ],
            Configuration::OWNER   => $conf->getOwner(),
            Options::N_EMAIL       => $Options->getNewEmail(),
            Options::F_OPT_IN      => $Options->getForceOptIn(),
            Options::F_OPT_OUT     => $Options->getForceOptOut(),
            Options::F_P_OPT_IN    => $Options->getForcePhoneOptIn(),
            Options::F_P_OPT_OUT   => $Options->getForcePhoneOptOut(),
            Options::TAGS_SCORING  => $Options->getTagScoring(),
            Options::TAGS          => $Options->getTags(),
            Options::R_TAGS        => $Options->getRemoveTags(),
            Contact::BIRTHDAY      => $Contact->getBirthday(),// attention
            Address::PROVINCE      => $Address->getProvince(),// attention
            Options::LANG          => $Options->getLang(),
            Properties::PROPERTIES => $Properties->get()
        ];

        //getIsSubscribed() is used for export only
        if ($Contact->getOptions()->getIsSubscribed()) {
            $contactRequestArray[Options::F_OPT_IN]    = true;
            $contactRequestArray[Options::F_OPT_OUT]   = false;
            $contactRequestArray[Options::F_P_OPT_IN]  = true;
            $contactRequestArray[Options::F_P_OPT_OUT] = false;
        } else {
            $contactRequestArray[Options::F_OPT_IN]    = false;
            $contactRequestArray[Options::F_OPT_OUT]   = false;
            $contactRequestArray[Options::F_P_OPT_IN]  = false;
            $contactRequestArray[Options::F_P_OPT_OUT] = false;
        }

        return DataHelper::filterDataArray($contactRequestArray);
    }

    /**
     * @param Contact $Contact
     * @param ConfigurationInterface $conf
     * @return array
     */
    public static function toArray(Contact $Contact, ConfigurationInterface $conf)
    {
        $Address    = $Contact->getAddress();
        $Options    = $Contact->getOptions();
        $Properties = $Contact->getProperties();
        $Consents   = $Contact->getConsents();

        $contactRequestArray = [
            Configuration::CLIENT_ID => $conf->getClientId(),
            Options::ASYNC      => $Options->getAsync(),
            Contact::CONTACT    => [
                Contact::EMAIL   => $Contact->getEmail(),
                Contact::FAX     => $Contact->getFax(),
                Contact::NAME    => $Contact->getName(),
                Contact::PHONE   => $Contact->getPhone(),
                Contact::COMPANY => $Contact->getCompany(),
                Contact::EXT_ID  => $Contact->getExternalId(),
                Contact::STATE   => $Contact->getState(),
                Contact::ADDRESS => [
                    Address::STREET_AD => $Address->getStreetAddress(),
                    Address::ZIP_CODE  => $Address->getZipCode(),
                    Address::CITY      => $Address->getCity(),
                    Address::COUNTRY   => $Address->getCountry()
                ],
            ],
            Configuration::OWNER                => $conf->getOwner(),
            Options::N_EMAIL                    => $Options->getNewEmail(),
            Options::F_OPT_IN                   => $Options->getForceOptIn(),
            Options::F_OPT_OUT                  => $Options->getForceOptOut(),
            Options::F_P_OPT_IN                 => $Options->getForcePhoneOptIn(),
            Options::F_P_OPT_OUT                => $Options->getForcePhoneOptOut(),
            Options::TAGS_SCORING               => $Options->getTagScoring(),
            Options::TAGS                       => $Options->getTags(),
            Options::R_TAGS                     => $Options->getRemoveTags(),
            Options::LOYALTY_PROGRAM            => $Options->getLoyaltyProgram(),
            Contact::BIRTHDAY                   => $Contact->getBirthday(),// attention
            Address::PROVINCE                   => $Address->getProvince(),// attention
            Options::LANG                       => $Options->getLang(),
            Properties::PROPERTIES              => $Properties->get(),
            ConsentsCollection::CONSENT_DETAILS => $Consents ? $Consents->toArray() : '', //@todo secure other assignments against errors
        ];

        if (self::isSubscriptionStatusNoChangeChecker($Contact)) {
            $contactRequestArray[Options::F_OPT_IN]              = false;
            $contactRequestArray[Options::F_OPT_OUT]             = false;
            $contactRequestArray[Options::F_P_OPT_IN]            = false;
            $contactRequestArray[Options::F_P_OPT_OUT]           = false;
            $contactRequestArray[ApiDoubleOptIn::U_API_D_OPT_IN] = false;
        }

        if ($Contact->getOptions()->getIsUnSubscribes()) {
            $contactRequestArray[Options::F_OPT_IN]    = false;
            $contactRequestArray[Options::F_OPT_OUT]   = true;
            $contactRequestArray[Options::F_P_OPT_IN]  = false;
            $contactRequestArray[Options::F_P_OPT_OUT] = true;
        }

        if ($Contact->getOptions()->getIsSubscribes()) {
            $contactRequestArray[Options::F_OPT_IN]    = true;
            $contactRequestArray[Options::F_OPT_OUT]   = false;
            $contactRequestArray[Options::F_P_OPT_IN]  = true;
            $contactRequestArray[Options::F_P_OPT_OUT] = false;
        }

        if ($Contact->getOptions()->getIsSubscribesNewsletter()) {
            $contactRequestArray[Options::F_OPT_IN]    = true;
            $contactRequestArray[Options::F_OPT_OUT]   = false;
        }

        if ($Contact->getOptions()->getIsSubscribesMobile()) {
            $contactRequestArray[Options::F_P_OPT_IN]  = true;
            $contactRequestArray[Options::F_P_OPT_OUT] = false;
        }

        if ($Contact->getOptions()->getIsUnSubscribesNewsletter()) {
            $contactRequestArray[Options::F_OPT_IN]    = false;
            $contactRequestArray[Options::F_OPT_OUT]   = true;
        }

        if ($Contact->getOptions()->getIsUnSubscribesMobile()) {
            $contactRequestArray[Options::F_P_OPT_IN]   = false;
            $contactRequestArray[Options::F_P_OPT_OUT]  = true;
        }

        if (!self::apiDoubleOptInChecker($Contact, $conf)) {
            return DataHelper::filterDataArray($contactRequestArray);
        }

        $ApiDoubleOptIn = $conf->getApiDoubleOptIn();

        //API with new attributes
        $contactRequestArray = array_merge(
            $contactRequestArray, [
            ApiDoubleOptIn::U_API_D_OPT_IN    => $ApiDoubleOptIn->getEnabled(),
            ApiDoubleOptIn::D_OPT_IN_EMAIL_ID => $ApiDoubleOptIn->getEmailId(),
            ApiDoubleOptIn::D_OPT_IN_LANG     => $ApiDoubleOptIn->getLang()
        ]);

        return DataHelper::filterDataArray($contactRequestArray);
    }

    /**
     * Checks if subscription status no change
     * @param Contact $Contact
     * @return bool
     */
    public static function isSubscriptionStatusNoChangeChecker(Contact $Contact)
    {
        return (!$Contact->getOptions()->getIsSubscribes()
            && $Contact->getOptions()->getIsSubscriptionStatusNoChange()
            && !$Contact->getOptions()->getForceOptIn());
    }

    /**
     * Checking for need add apiDoubleOptIn to request;
     * @param ConfigurationInterface $conf
     * @param Contact $Contact;
     * @return bool
     */
    public static function apiDoubleOptInChecker(Contact $Contact, ConfigurationInterface $conf)
    {
        $isApiDoubleOptInEnabled = $conf->getApiDoubleOptIn()->getEnabled();

        if (!$isApiDoubleOptInEnabled) {
            return false;
        }

        if ($Contact->getOptions()->getIsSubscribes() || $Contact->getOptions()->getIsSubscribesNewsletter()) {
           return true;
        }

        return false;
    }
}
