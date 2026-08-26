<?php


namespace SALESmanago\Entity\Event;


use DateTime;
use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Exception\Exception;
use SALESmanago\Helper\EntityDataHelper;

class Event extends AbstractEntity
{

    const
        CONTACT_EVENT  = 'contactEvent',
        CONTACT_ID     = 'contactId',
        EVENT_ID       = 'eventId',
        EMAIL          = 'email',
        DATE           = 'date',
        DESCRIPTION    = 'description',
        PRODUCTS       = 'products',
        LOCATION       = 'location',
        VALUE          = 'value',
        EXT_EVENT_TYPE = 'contactExtEventType',
        DETAIL         = 'detail',
        EXT_ID         = 'externalId',
        SHOP_DOMAIN    = 'shopDomain';

    const
        EVENT_TYPE_CART         = "CART",
        EVENT_TYPE_PURCHASE     = "PURCHASE",
        EVENT_TYPE_CANCELLATION = "CANCELLATION",
        EVENT_TYPE_RETURN       = "RETURN",
        EVENT_TYPE_OTHER        = "OTHER";

    /**
     * @var string SALESmanago contactId
     */
    private $contactId = null;

    /**
     * @var string contact email;
     */
    private $email = null;

    /**
     * @var string SM event id
     */
    private $eventId = null;

    /**
     * @var int unixtimestamp - 13 numbers
     */
    private $date = null;

    /**
     * @var string description of event
     */
    private $description = null;

    /**
     * @var mixed product ids, separated by comma or array of product ids
     */
    private $products = null;

    /**
     * @var string unique shop id
     */
    private $location = null;

    /**
     * @var string value of event
     */
    private $value = null;

    /**
     * @var string ext event type
     */
    private $contactExtEventType = null;

    protected $accessibleContactExtEventTypes = [
        'PURCHASE',
        'CART',
        'VISIT',
        'PHONE_CALL',
        'OTHER',
        'RESERVATION',
        'CANCELLED',
        'ACTIVATION',
        'MEETING',
        'OFFER',
        'DOWNLOAD',
        'LOGIN',
        'TRANSACTION',
        'CANCELLATION',
        'RETURN',
        'SURVEY',
        'APP_STATUS',
        'APP_TYPE_WEB',
        'APP_TYPE_MANUAL',
        'APP_TYPE_RETENTION',
        'APP_TYPE_UPSALE',
        'LOAN_STATUS',
        'LOAN_ORDER',
        'FIRST_LOAN',
        'REPEATED_LOAN'
    ];

    /**
     * @var array of strings optional information for event;
     */
    private $details = [];

    /**
     * @var string - id of event from system
     */
    private $extId = null;

    /**
     * @var string shop domain
     */
    private $shopDomain = null;

    /**
     * @var bool
     */
    private $forceOptIn = false;

    /**
     * Event constructor.
     * @param array $data
     * @throws Exception
     */
    public function __construct($data = [])
    {
        if(!empty($data)){
            $this->setDataWithSetters($data);
        }
    }

    /**
     * @param $data
     * @return $this
     * @throws Exception
     */
    public function set($data) {
        $this->setDataWithSetters($data);
        return $this;
    }

    public function setEventId($param)
    {
        $this->eventId = $param;
        return $this;
    }

    public function getEventId()
    {
        return $this->eventId;
    }

    public function setContactId($param)
    {
        $this->contactId = $param;
        return $this;
    }

    public function getContactId()
    {
        return $this->contactId;
    }

    public function setEmail($param)
    {
        $this->email = $param;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setForceOptIn($param)
    {
        $this->forceOptIn = $param;
        return $this;
    }

    public function getForceOptIn()
    {
        return $this->forceOptIn;
    }

    public function setDate($param)
    {
        if ($param == null) {
            return $this;
        }

        if (is_bool($param) || empty($param)) {
            throw new Exception('Passed argument isn\'t timestamp');
        } elseif (EntityDataHelper::isUnixTime($param)) {
            $this->date = $param*1000;
        } elseif ($param instanceof DateTime) {
            $this->date = strtotime($param->format('Y-m-d H:i:sP'))*1000;
        } elseif (EntityDataHelper::isTimestamp($param)) {
            try {
                $date = new DateTime($param);
            } catch (\Exception $e) {
                throw new Exception($e->getMessage());
            }
            $this->date = strtotime($date->format('Y-m-d H:i:sP'))*1000;
        } else {
            throw new Exception('Passed argument isn\'t timestamp');
        }
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDescription($param)
    {
        $this->description = $param;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setProducts($param)
    {
        $this->products = (is_array($param)) ? implode(',', $param) : $param;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setLocation($param)
    {
        $this->location = $param;
        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setValue($param)
    {
        $this->value = $param;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setContactExtEventType($param)
    {
        $this->contactExtEventType = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactExtEventType()
    {
        return $this->contactExtEventType;
    }

    public function setDetails($param)
    {
        if (!is_array($param)) {
            throw new Exception('Parameter must be an array for setDetails');
        }

        foreach ($param as $key => $val) {
            $this->setDetail($val, $key);
        }

        return $this;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetail($param, $nb = null)
    {
        if ($nb === null) {
            $this->details[] = $param;
        } else {
            $this->details[$nb] = $param;
        }
        return $this;
    }

    public function getDetail($nb = null)
    {
        return ($nb === null)
            ? $this->details
            : $this->details[$nb];
    }

    public function setExternalId($param)
    {
        $this->extId = $param;
        return $this;
    }

    public function getExternalId()
    {
        return $this->extId;
    }

    public function setShopDomain($param)
    {
        $this->shopDomain = $param;
        return $this;
    }

    public function getShopDomain()
    {
        return $this->shopDomain;
    }

    /**
     * Check if event is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (empty($this->products) && empty($this->value));
    }
}
