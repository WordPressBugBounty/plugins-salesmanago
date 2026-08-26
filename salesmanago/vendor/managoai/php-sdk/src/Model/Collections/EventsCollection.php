<?php


namespace SALESmanago\Model\Collections;

use SALESmanago\Entity\Event\Event;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\EventModel;

class EventsCollection extends AbstractCollection
{
    /**
     * @throws Exception
     * @param Event $object
     * @return AbstractCollection|void
     */
    public function addItem($object)
    {
        if(!($object instanceof Event)) {
            throw new Exception('Not right entity type');
        }

        array_push($this->collection, $object);
        return $this;
    }

    /**
     * Parse Collection to array
     * @return array
     */
    public function toArray(): array
    {
        $events = [];
        if (!$this->isEmpty()) {
            array_walk($this->collection, function ($event, $key) use (&$events) {
                array_push($events, EventModel::toArray($event));
            });
        }
        return ['events' => $events];
    }
}
