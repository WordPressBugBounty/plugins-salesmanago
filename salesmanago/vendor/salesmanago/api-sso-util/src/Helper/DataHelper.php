<?php


namespace SALESmanago\Helper;

use SALESmanago\Entity\Event\Event;

class DataHelper
{
    public static $maxFieldLengths = array(
        Event::CONTACT_ID     => 0, //do not trim contactId so it will show up in the logs
        Event::EVENT_ID       => 0, //do not trim eventId so it will show up in the logs
        Event::EMAIL          => 0, //do not trim email so it will show up in the logs
        Event::DESCRIPTION    => 2048,
        Event::PRODUCTS       => 512,
        Event::LOCATION       => 255,
        Event::VALUE          => 22,
        Event::EXT_EVENT_TYPE => 255,
        Event::DETAIL         => 255,
        Event::EXT_ID         => 255,
        Event::SHOP_DOMAIN    => 255
    );
    /**
     * Unset empty array values
     *
     * @param array $data
     * @return array
     */
    public static function filterDataArray($data)
    {
        $filteredData = array();
        if (!is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $value) {
            //For arrays call recursively:
            if (is_array($value)) {
                $filteredArray = self::filterDataArray($value);
                if(!empty($filteredArray)) {
                    $filteredData[$key] = $filteredArray;
                }
            //For strings trim, truncate and remove empty
            } elseif (is_string($value)) {
                if (strpos($key, Event::DETAIL) !== false
                    && !empty($value)) {
                    $filteredData[$key] = substr(trim($value), 0, self::$maxFieldLengths[Event::DETAIL]);
                } elseif (!empty(self::$maxFieldLengths[$key])
                    && !empty($value)) {
                    $filteredData[$key] = substr(trim($value), 0, self::$maxFieldLengths[$key]);
                } elseif (!empty($value)) {
                    $filteredData[$key] = trim($value);
                }

            //For numerics and booleans remove nulls
            } elseif ($value !== null) {
                $filteredData[$key] = $value;
            }
        }
        return $filteredData;
    }
}