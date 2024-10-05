<?php


namespace SALESmanago\Helper;


use DateTime;
use Exception;

class EntityDataHelper extends DataHelper
{
    public static function setStrFromArr($param, $glue = ' ')
    {
        if (!is_array($param)) {
            return $param;
        }

        $param = self::filterArr($param);
        if (!empty($param)) {
            return trim(implode($glue, $param));
        }

        return '';
    }

    public static function filterArr($array)
    {
        return array_filter(
            $array,
            function ($value) {
                return !empty($value);
            }
        );
    }

    /**
     * @param string $string
     * @return bool
     */
    public static function isTimestamp($string)
    {
        if (!$string) {
            return false;
        }

        try {
            new DateTime($string);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed $param - int || string
     * @return bool
     */
    public static function isUnixTime($param)
    {
        $answer = ((intval($param)) == $param)
            && (intval($param) <= PHP_INT_MAX)
            && (intval($param) >= ~PHP_INT_MAX);

        if(is_string($param)){
            $answer = ($answer && ((1 === preg_match( '~^[1-9][0-9]*$~', $param))));
        }

        return $answer;
    }
}