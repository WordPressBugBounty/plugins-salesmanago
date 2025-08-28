<?php

namespace bhr\Admin\Model;

if(!defined('ABSPATH')) exit;

use SALESmanago\Entity\Contact\Address;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Options;
use SALESmanago\Entity\Contact\Properties;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Exception\Exception;

class ReportingModel
{
    const
        REPORTING_ENDPOINT  = "https://survey.salesmanago.com",
        PLATFORM            = 'platform',
        PLATFORM_NAME       = 'WORDPRESS',
        PLUGIN_VERSION      = 'pluginVersion';

    private $Contact;
    private $Event;

    public function __construct()
    {
        $this->Contact    = new Contact();
        $this->Event      = new Event();
        $Address          = new Address();
        $Options          = new Options();
        $Properties       = new Properties();
        $this->Contact
            ->setAddress($Address)
            ->setOptions($Options)
            ->setProperties($Properties);
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->Contact;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->Event;
    }

    /**
     * @param $vendorEndpoint
     * @param $conf
     *
     * @throws Exception
     */
    public function buildContact($vendorEndpoint, $conf)
    {
        $this->Contact
            ->setName($_SERVER['SERVER_NAME'])
            ->setCompany($conf->getLocation());

        $this->Contact->getAddress()
            ->setCity($vendorEndpoint)
            ->setStreetAddress(SM_VERSION)
            ->setCountry(Helper::getUserLocale());

        $this->Contact->getOptions()
            ->setLang(Helper::getUserLocale());

        $this->Contact->getProperties()
            ->setItem(self::PLATFORM, self::PLATFORM_NAME)
            ->setItem(self::PLUGIN_VERSION, SM_VERSION);
    }

    /**
     * @param string $type
     * @param string $longDesc - to be send as description (without char limit)
     * @param string $det1 - to be send as detail 1
     * @param string $det2 - to be send as detail 2
     */
    public function buildEvent($type = 'OTHER', $longDesc = '', $det1 = '', $det2 = '')
    {
        $this->Event
            ->setContactExtEventType($type)
            ->setEventId(substr($longDesc, 0, 2048))
            ->setDetail($det1, 1)
            ->setDetail($det2, 2);
    }
}
