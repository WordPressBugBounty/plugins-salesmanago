<?php

namespace SALESmanago\Exception;

use DateTime;

class ApiV3Exception extends Exception
{
    const
        WARNING = 'warn',
        ERROR = 'err',
        ERRORS = 'ERRORS',
        REASON_CODE = "Reason code";

    /**
     * @var array
     */
    private $codes = [];

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $viewMessage = [];

    /**
     * Sets error codes
     *
     * @param array $codes
     * @return $this
     */
    public function setCodes(array $codes)
    {
        $this->codes = $codes;
        return $this;
    }

    /**
     * Return error codes
     *
     * @return array
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Do not use outside class. Use instead getAllViewMessages()
     * @return array
     */
    private function getAllViewMessageProp()
    {
        return $this->viewMessage;
    }

    /**
     * @param array $viewMessage
     * @return $this
     */
    private function setAllViewMessageProp(array $viewMessage)
    {
        $this->viewMessage = $viewMessage;
        return $this;
    }

    /**
     * Set messages
     *
     * @return $this
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Return messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Combined error codes with messages [errorCode => errorMessage]
     *
     * @return array
     */
    public function getCombined()
    {
        $combined = [];
        foreach ($this->getCodes() as $index => $code) {
            $combined[$code] = $this->messages[$index];
        }
        return $combined;
    }

    /**
     * Based on statusCode return message which may be
     * translated to different language
     *
     * @param int $code
     * @param string $field
     * @param string $fieldType
     * @return string
     */
    public function getParsedMessage($code, $field = null, $fieldType = null)
    {
        switch($code) {
            case 10:
                $message = 'Authentication failed. Make sure your API Key is valid.';
                break;
            case 11:
                $message = 'The following field exceeds limit: $field$';
                break;
            case 12:
                $message = 'The following field was too long and was trimmed: $field$';
                break;
            case 13:
                $message = 'Missing required field. Required $field$';
                break;
            case 14:
                $message = 'The following field is of wrong type. $field$ must be of type $fieldType$';
                break;
            case 15:
                $message = 'A resource with the following field already exists in SALESmanago: $field$';
                break;
            case 16:
                $message = 'Trying to access resource with identifier not present in SALESmanago';
                break;
            case 17:
                $message = 'Trying to set a required value as null';
                break;
            case 18:
                $message = 'Value not matching a required structure (RegEx). For the field: $field$';
                break;
            case 19:
                $message = 'Trying to add a resource above available limit';
                break;
            default:
                $message = 'Success';
                break;
        }

        if ($field) {
            $message = str_replace('$field$', $field, $message);
        }

        if ($fieldType) {
            $message = str_replace('$fieldType$', $fieldType, $message);
        }

        return $message;
    }

    /**
     * @param string|null $type
     * @return void
     */
    public function goThroughAllCodes($type = null)
    {
        $codes = $this->getCodes();
        $messages = $this->getMessages();
        $fieldArray = [];
        $viewMessage = [];

        foreach ($codes as $key => $code) {
            if ($this->checkTypeToCode($type, $code)) {
                continue;
            }

            $field = null;
            $fieldType = null;
            if (10 < $code && $code < 19) {
                $field = explode('|', $messages[$key])[0];
                switch ($code) {
                    case 14:
                        $fieldWithType = $field;
                        $field = explode(':', $fieldWithType)[0];
                        $fieldType = explode(':', $fieldWithType)[1];
                        break;
                }
            }

            array_push($fieldArray, $field);
            $viewMessage[$key] = $this->getParsedMessage($code, $field, $fieldType);
        }
        $this->setFields($fieldArray);
        $this->setAllViewMessageProp($viewMessage);
    }

    /**
     * @see https://docs.salesmanago.com/v3/#general-errors-and-warnings
     * @param string $type - self::WARNING || self::ERROR
     * @return array
     */
    public function getAllViewMessages($type = null)
    {
        $this->goThroughAllCodes($type);
        return $this->getAllViewMessageProp();
    }

    /**
     * Used for go through status codes and set dynamically required fields.
     *
     * @return $this
     */
    public function setRequiredFields()
    {
        $this->goThroughAllCodes();
        return $this;
    }

    /**
     * @param bool $withoutDuplicatesCodes - return log messages without duplicates error codes for error
     * @param bool| null $type - type of errors self::ERRORS || salf::WARNING, null - returns all types
     * @param string|null $dateTime - date time of exception;
     * @param array|null $args - additional arguments
     * @return string
     */
    public function getAllLogMessages(
        $withoutDuplicatesCodes = false,
        $type = null,
        $dateTime = null,
        $args = []
    ) {
        $codes = $this->getCodes();
        $messages = $this->getMessages();

        $errorCodesWithMessages = [];

        $logMessage = self::EXCEPTION_HEADER_NAME;
        $logMessage .= ($dateTime != null) ? $dateTime : (new DateTime())->format("Y-m-d\\TH:i:sO") . PHP_EOL;
        $logMessage .= self::FILE . $this->getFile() . PHP_EOL;
        $logMessage .= self::LINE . $this->getLine() . PHP_EOL;
        $logMessage .= ($args != null) ? json_encode($args) . PHP_EOL : '';
        $logMessage .= self::ERRORS . ': ';

        foreach ($codes as $key => $code) {
            if ($this->checkTypeToCode($type, $code)) {
                continue;
            }

            if ($withoutDuplicatesCodes && !array_key_exists($key, $errorCodesWithMessages)) {
                $errorCodesWithMessages[$code] = self::REASON_CODE . ': ' . $code . ' ';
                $errorCodesWithMessages[$code].= self::MESSAGE . $messages[$key];
            } else {
                $errorCodesWithMessages[$key] = self::REASON_CODE . ': ' . $code . ' ';
                $errorCodesWithMessages[$key].= self::MESSAGE . $messages[$key];
            }
        }

        $logMessage .= (!empty($errorCodesWithMessages))
            ? json_encode($errorCodesWithMessages) . PHP_EOL
            : PHP_EOL;

        return $logMessage;
    }

    /**
     * Checks if the selected type matches the error code
     *
     * @param string $type - self::WARNING || self::ERROR
     * @param int $code
     * @return bool
     */
    protected function checkTypeToCode($type, $code)
    {
        return (($type === self::WARNING && $code !== 12)
        || ($type === self::ERROR && $code === 12));
    }
}
