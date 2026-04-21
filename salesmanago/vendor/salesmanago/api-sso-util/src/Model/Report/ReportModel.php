<?php


namespace SALESmanago\Model\Report;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Exception\Exception;

class ReportModel
{
    const
        ACT_LOGOUT          = 'logout',
        ACT_LOGIN           = 'login',
        ACT_EXPORT          = 'export',
        ACT_EXPORT_EVENT    = 'export',
        ACT_EXPORT_CONTACTS = 'export',
        ACT_EXCEPTION       = 'exception',
        ACT_UPDATE          = 'update',
        ACT_DEACTIVATION    = 'deactivation',
        ACT_DELETE          = 'delete',
        ACT_UNKNOWN         = 'unknown',
        //use only for tests:
        ACT_TEST            = 'test',
        ACT_CREATE_CATALOG  = 'create_catalog',
        ACT_REMOVED_CATALOG = 'removed_catalog',//basically it is disabling product catalog in integration
        ACT_DELETE_CATALOG  = 'delete_catalog',

        EXPORT_TYPE          = 'EXPORT_TYPE',
        PACKAGES             = 'PACKAGES',
        ITEMS                = 'ITEMS',
        STORE                = 'STORE',

        EXPORT_TYPE_PRODUCTS = 'EXPORT_TYPE_PRODUCTS',
        EXPORT_TYPE_CARTS    = 'EXPORT_TYPE_CARTS',
        EXPORT_TYPE_PURCHASE = 'EXPORT_TYPE_PURCHASE',
        EXPORT_TYPE_CONTACTS = 'EXPORT_TYPE_CONTACTS';

    /**
     * @var string[] - mapping actual action to event type in SALESmanago
     */
    protected $actionToEventType = [
        //client actions:
        self::ACT_LOGOUT          => 'RETURN',
        self::ACT_LOGIN           => 'LOGIN',
        self::ACT_EXPORT          => 'TRANSACTION',
        //integration/plugin action:
        self::ACT_EXCEPTION       => 'CANCELLATION',
        self::ACT_UPDATE          => 'ACTIVATION',
        self::ACT_DEACTIVATION    => 'CANCELLATION',
        self::ACT_DELETE          => 'APP_TYPE_RETENTION',
        self::ACT_UNKNOWN         => 'OTHER',
        self::ACT_TEST            => 'SURVEY',
        self::ACT_CREATE_CATALOG  => 'VISIT',
        self::ACT_REMOVED_CATALOG => 'VISIT',
        self::ACT_DELETE_CATALOG  => 'VISIT',
    ];

    /**
     * @var ConfigurationInterface
     */
    private $conf;

    /**
     * ReportModel constructor.
     *
     * @param ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
    }

    /**
     * @return Contact return new instance of contact
     */
    private function getContact(): Contact
    {
        return new Contact();
    }

    /**
     * @return Event return new instance of Event
     */
    private function getEvent(): Event
    {
        return new Event();
    }

    /**
     * Generate client information for reporting service
     *
     * @param string $actionType - action tag
     * @return Contact
     */
    public function getClientAsContact($actionType = self::ACT_UNKNOWN)
    {
        $Contact = $this->getContact()
            ->setEmail($this->conf->getOwner())
            ->setName(preg_replace("(^https?://)", "", $this->conf->getPlatformDomain()))
            ->setExternalId($this->conf->getClientId() ?? time())
            ->setCompany($this->conf->getPlatformName());

        $Contact->setAddress(
            $Contact->getAddress()
                ->setStreetAddress($this->conf->getVersionOfIntegration())
                ->setCountry(strtoupper($this->conf->getPlatformCountry()))
        );

        $Contact->setOptions(
            $Contact->getOptions()
                ->setLang(strtolower($this->conf->getPlatformLang()))
                ->appendTags(
                    [
                        'LANG_' . str_replace(' ', '_', strtolower($this->conf->getPlatformLang())),
                        str_replace(' ', '_', strtoupper($this->conf->getPlatformCountry())),
                        str_replace(' ', '_', strtoupper($this->conf->getPlatformName())),
                        str_replace(
                            ' ',
                            '_',
                            strtoupper($this->conf->getPlatformName().$this->conf->getPlatformVersion())
                        ),
                        'PHP_' . $this->conf->getPhpVersion()
                    ]
                )
                ->setIsSubscriptionStatusNoChange(false)
                ->setIsSubscribes(true)
        );

        switch ($actionType) {
            case self::ACT_CREATE_CATALOG:
                $Contact->setOptions(
                    $Contact->getOptions()
                        ->setLang(strtolower($this->conf->getPlatformLang()))
                        ->appendTags(
                            [
                                'PRODUCT_CATALOGS', 'CATALOG_CREATED'
                            ]
                        )
                );
                break;
            case self::ACT_REMOVED_CATALOG:
                $Contact->setOptions(
                    $Contact->getOptions()
                        ->setLang(strtolower($this->conf->getPlatformLang()))
                        ->appendTags(
                            [
                                'PRODUCT_CATALOG_REMOVED'
                            ]
                        )
                );
                break;
            case self::ACT_DELETE_CATALOG:
                $Contact->setOptions(
                    $Contact->getOptions()
                        ->setLang(strtolower($this->conf->getPlatformLang()))
                        ->appendTags(
                            [
                                'CATALOG_DELETED_FROM_SM'
                            ]
                        )
                );
                break;
        }

        return $Contact;
    }

    /**
     * Generate client information for reporting service with additional tags
     *
     * @param array $tags
     * @param string $actionType - action tag
     * @return Contact
     */
    public function getClientAsContactWithTags(
        array  $tags = [],
        string $nameOfReportingItem = '',
        string $actionType = self::ACT_UNKNOWN
    ): Contact
    {
        $Contact = $this->getClientAsContact($actionType);

        if (!empty($tags)) {
            $Contact->setOptions(
                $Contact
                    ->getOptions()
                    ->appendTags($tags)
            );
        }

        if (!empty($nameOfReportingItem)) {
            $Contact->setOptions(
                $Contact
                    ->getOptions()
                    ->appendTags([$nameOfReportingItem])
            );
        }


        return $Contact;
    }

    /**
     * Generate event information for reporting service
     *
     * @param string $actionType one of const
     * @param array $arr
     * @return Event
     * @throws Exception
     */
    public function getActionAsEvent(
        string $actionType = self::ACT_UNKNOWN,
        array  $arr = []
    ): Event
    {
        $Event = $this->getEvent()
            ->setEmail($this->conf->getOwner())
            ->setDate(time())
            ->setProducts($this->conf->getPlatformVersion())
            ->setLocation($this->conf->getVersionOfIntegration())
            ->setShopDomain(preg_replace("(^https?://)", "", $this->conf->getPlatformDomain()))
            ->setDetail("php_version_{$this->conf->getPhpVersion()}", 1)
            ->setDetail("plugin_version_{$this->conf->getVersionOfIntegration()}", 2)
            ->setDetail('Date:' . gmdate("Y-m-d\TH:i:s\Z"), 3)
            ->setDescription(substr(json_encode($this->conf->toArray()), 0, 250))
            ->setContactExtEventType($this->actionToEventType[$actionType]);

        if (!empty($arr) && $actionType != self::ACT_EXPORT) {
            $Event->setDescription(json_encode($arr));
        }

        switch ($actionType) {
            case self::ACT_CREATE_CATALOG:
                $Event->setDetail('CATALOG_CREATED', 8);
                break;
            case self::ACT_REMOVED_CATALOG:
                $Event->setDetail('CATALOG_REMOVED', 8);
                break;
            case self::ACT_DELETE_CATALOG:
                $Event->setDetail('CATALOG_DELETED_FROM_SM', 8);
                break;
            case self::ACT_EXPORT:
                $Event
                    ->setDetail($arr[self::EXPORT_TYPE], 4)
                    ->setDetail($arr[self::PACKAGES], 5)
                    ->setDetail($arr[self::ITEMS], 6)
                    ->setDetail($arr[self::STORE], 7);
                break;
        }

        return $Event;
    }

    /**
     * Generate event information for reporting service with additional details
     *
     * @param array $details - additional details in key=>value array
     * @param string $typeOfSMEvent
     * @param string $nameOfReportingItem
     * @param string $actionType - one of const
     * @return Event
     * @throws Exception
     */
    public function getActionAsEventWithDetails(
        array  $details = [],
        string $typeOfSMEvent = 'OTHER',
        string $nameOfReportingItem = '',
        string $actionType = self::ACT_UNKNOWN
    ): Event
    {
        $Event = $this->getActionAsEvent($actionType);

        if (!empty($details)) {
            foreach ($details as $key => $value) {
                $Event->setDetail($value, $key);
            }
        }

        if (!empty($nameOfReportingItem)) {
            $Event->setDetail($nameOfReportingItem, 8);
        }

        if ($typeOfSMEvent) {
            $Event->setContactExtEventType($typeOfSMEvent);
        }

        return $Event;
    }
}
