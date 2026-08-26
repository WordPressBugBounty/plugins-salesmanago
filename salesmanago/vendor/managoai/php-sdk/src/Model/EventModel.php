<?php


namespace SALESmanago\Model;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Options;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Entity\Configuration;
use SALESmanago\Helper\DataHelper;

class EventModel
{
    protected $Event;
    protected $conf;

    public function __construct(Event $Event, ConfigurationInterface $conf)
    {
        $this->Event = $Event;
        $this->conf = $conf;
    }

    public function getEventForUnionTransfer()
    {
        $eventRequestArray = [
            Configuration::CLIENT_ID  => $this->conf->getClientId(),
            Configuration::OWNER      => $this->conf->getOwner(),
            Options::F_OPT_IN    => $this->Event->getForceOptIn(),
            Event::EMAIL         => $this->Event->getEmail(),
            Event::CONTACT_ID    => $this->Event->getContactId(),
            Event::CONTACT_EVENT => [
                Event::EVENT_ID       => $this->Event->getEventId(),
                Event::DATE           => $this->Event->getDate(),
                Event::DESCRIPTION    => $this->Event->getDescription(),
                Event::PRODUCTS       => $this->Event->getProducts(),
                Event::LOCATION       => $this->Event->getLocation(),
                Event::VALUE          => $this->Event->getValue(),
                Event::EXT_EVENT_TYPE => $this->Event->getContactExtEventType(),
                Event::EXT_ID         => $this->Event->getExternalId(),
                Event::SHOP_DOMAIN    => $this->Event->getShopDomain()
            ]
        ];

        $eventRequestArray[Event::CONTACT_EVENT] = array_merge(
            $eventRequestArray[Event::CONTACT_EVENT],
            self::getEventDetailsRequestArray($this->Event)
        );
        return $this->doubleIdentifierRemove(DataHelper::filterDataArray($eventRequestArray));
    }

    /**
     * @param Event $Event
     * @return array
     */
    public static function toArray(Event $Event)
    {
            return self::doubleIdentifierRemove(DataHelper::filterDataArray([
            Event::EMAIL         => $Event->getEmail(),
            Event::CONTACT_ID    => $Event->getContactId(),
            Event::CONTACT_EVENT => array_merge([
                Event::EVENT_ID       => $Event->getEventId(),
                Event::DATE           => $Event->getDate(),
                Event::DESCRIPTION    => $Event->getDescription(),
                Event::PRODUCTS       => $Event->getProducts(),
                Event::LOCATION       => $Event->getLocation(),
                Event::VALUE          => $Event->getValue(),
                Event::EXT_EVENT_TYPE => $Event->getContactExtEventType(),
                Event::EXT_ID         => $Event->getExternalId(),
                Event::SHOP_DOMAIN    => $Event->getShopDomain()
            ], self::getEventDetailsRequestArray($Event)
            )]));
    }

    /**
     * Get evnt details from Entity & set event detail array keys for request
     * @param Event $Event
     * @return array of event details
     */
    protected static function getEventDetailsRequestArray(Event $Event)
    {
        $eventDetails = $Event->getDetails();

        if (empty($eventDetails)) {
            return $eventDetails;
        }

        //sort array into right order:
        ksort($eventDetails);

        //next one help to start set array from 1 (detail1, detail2...) (not from 0 like detail0):
        array_unshift($eventDetails,"");
        unset($eventDetails[0]);

        foreach ($eventDetails as $key => $detail) {
            $eventDetails[Event::DETAIL.$key] = $detail;
            unset($eventDetails[$key]);
        }

        return $eventDetails;
    }

    /**
     * Removes email id client_id exist
     *
     * @param array $event - basically Entity\Event translate to array;
     * @return array $event
     */
    protected static function doubleIdentifierRemove($event)
    {
        if (isset($event[Event::EMAIL]) && isset($event[Event::CONTACT_ID])) {
            unset($event[Event::EMAIL]);
        }

        return $event;
    }
}
