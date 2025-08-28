<?php

namespace SALESmanago\Entity\Contact;

use DateTime;
use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Exception\Exception;

use SALESmanago\Helper\EntityDataHelper;
use SALESmanago\Model\Collections\ConsentsCollection;

class Contact extends AbstractEntity implements ContactInterface
{
    const
        CONTACT  = 'contact',
        EMAIL    = 'email',
        C_ID     = 'contactId',
        FAX      = 'fax',
        NAME     = 'name',
        PHONE    = 'phone',
        COMPANY  = 'company',
        STATE    = 'state',
        BIRTHDAY = 'birthday',
        ADDRESS  = 'address',
        EXT_ID   = 'externalId',
        COOKIE_NAME = 'smclient';

    private $email         = null;
    private $contactId     = null;
    private $fax           = null;
    private $name          = null;
    private $phone         = null;
    private $company       = null;
    private $state         = null;
    private $birthday      = null;
    private $birthdayDay   = null;
    private $birthdayMonth = null;
    private $birthdayYear  = null;
    private $externalId    = null;

    private $score         = null;

    /**
     * @var Address|null
     */
    private $Address       = null;

    /**
     * @var Properties|null
     */
    private $Properties    = null;

    /**
     * @var Options|null
     */
    private $Options       = null;

    /**
     * @var ConsentsCollection|null
     */
    private $Consents      = null;

    /**
     * @param array $contactData;
     *
     * @return $this;
     * @throws Exception;
    */
    public function set($contactData) {
        $this->setDataWithSetters($contactData);
        return $this;
    }

    /**
     * @param $param - email
     * @return $this
     */
    public function setEmail($param)
    {
        $this->email = $param;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets contact id
     * @param $param - contactId
     * @return $this
     */
    public function setContactId($param)
    {
        $this->contactId = $param;
        return $this;
    }

    /**
     * @return mixed - contactId
     */
    public function getContactId(): ?string
    {
    	return $this->contactId;
    }

    /**
     * @param $param - contact name
     * @return $this
     */
    public function setName($param)
    {
        $this->name = is_array($param)
            ? EntityDataHelper::setStrFromArr($param)
            : $param;
        return $this;
    }

    /**
     * @return mixed $this->name;
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $param - fax nr
     * @return $this
     */
    public function setFax($param)
    {
        $this->fax = $param;
        return $this;
    }

    /**
     * @return mixed - $this->fax;
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param $param - phone nr.
     * @return $this
     */
    public function setPhone($param)
    {
        $this->phone = $param;
        return $this;
    }

    /**
     * @return mixed $this->phone;
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param $param - company name
     * @return $this
     */
    public function setCompany($param)
    {
        $this->company = $param;
        return $this;
    }

    /**
     * @return mixed $this->company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param $param - state
     * @return $this
     */
    public function setState($param)
    {
        $this->state = $param;
        return $this;
    }

    /**
     * @return $this->state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param $param - external id
     * @return $this
     */
    public function setExternalId($param)
    {
        $this->externalId = $param;
        return $this;
    }

    /**
     * @return $this->externalId
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param mixed $param
     * @return $this
     * @throws Exception
     */
    public function setBirthday($param)
    {
        if ($param == null) {
            return $this;
        }
        if (is_bool($param) || empty($param)) {
            throw new Exception('Passed argument isn\'t timestamp');
        } elseif ($param instanceof DateTime) {
            $this->birthday = $param->format('Ymd');
        } elseif (EntityDataHelper::isTimestamp($param)) {
            try {
                $birthday = new DateTime($param);
            } catch (\Exception $e) {
                throw new Exception($e->getMessage());
            }
            $this->birthday = $birthday->format('Ymd');
        } elseif (EntityDataHelper::isUnixTime($param)) {
            $this->birthday = gmdate("Ymd", intval($param));
        } else {
            throw new Exception('Passed argument isn\'t timestamp');
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param Address $ContactAddress
     * @return $this
     */
    public function setAddress(Address $ContactAddress)
    {
        $this->Address = $ContactAddress;
        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
    	return isset($this->Address)
		    ? $this->Address
		    : $this->Address = new Address();
    }

    /**
     * @param Options $Options
     * @return $this
     */
    public function setOptions(Options $Options)
    {
        $this->Options = $Options;
        return $this;
    }

    /**
     * @param array $options
     * @return Options
     */
    public function getOptions($options = [])
    {
    	return isset($this->Options)
		    ? $this->Options
		    : $this->Options = new Options($options);
    }

    /**
     * @param Properties $Properties
     * @return $this
     */
    public function setProperties(Properties $Properties)
    {
        $this->Properties = $Properties;
        return $this;
    }

    /**
     * @param array $properties
     * @return Properties
     */
    public function getProperties($properties = [])
    {
        return isset($this->Properties)
            ? $this->Properties
            : $this->Properties = new Properties($properties);
    }

    /**
     * @param $param
     * @return $this
     */
    public function setScore($param)
    {
        $this->score = $param;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScore()
    {
        return $this->score;
    }

    public function setBirthdayYear($param)
    {
        $this->birthdayYear = $param;
        return $this;
    }

    public function getBirthdayYear()
    {
        return $this->birthdayYear;
    }

    public function setBirthdayMonth($param)
    {
        $this->birthdayMonth = $param;
        return $this;
    }

    public function getBirthdayMonth($param)
    {
        return $this->birthdayMonth;
    }

    public function setBirthdayDay($param)
    {
        $this->birthdayDay = $param;
        return $this;
    }

    public function getBirthdayDay($param)
    {
        return $this->birthdayDay;
    }

    /**
     * @return ConsentsCollection|null
     */
    public function getConsents()
    {
        return $this->Consents;
    }

    /**
     * @param  ConsentsCollection $param
     *
     * @return Contact
     */
    public function setConsents($param)
    {
        $this->Consents = $param;

        return $this;
    }
}
