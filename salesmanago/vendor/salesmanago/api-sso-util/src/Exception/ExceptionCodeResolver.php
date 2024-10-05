<?php

namespace SALESmanago\Exception;

class ExceptionCodeResolver
{
    /*
     * Message classes:
     * 1XX - authorization errors
     * 2XX - single contact/event upsert errors
     * 3XX - batch contact/event upsert errors
     * 4XX - Guzzle client errors
     * 5XX - Configuration & settings reading/saving errors
     * 6XX - Other platform errors
     */

    /**
     * @param $message
     * @param null $errorClass
     * @return int
     */
    public static function codeFromResponseMessage($message, $errorClass = null)
    {
        $messageCodes = array(
            101 => 'User not authenticated',
            102 => 'Not authenticated' //Likely inactive account
        );
        if($key = array_search($message, $messageCodes)) {
            return intval($key);
        } elseif(!empty($errorClass)) {
            return intval($errorClass);
        }
        return 0;
    }
}