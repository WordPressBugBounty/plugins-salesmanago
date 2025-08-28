<?php

namespace SALESmanago\Entity;


use SALESmanago\Entity\Contact\Address;
use SALESmanago\Exception\Exception;

class Response extends AbstractEntity
{
    const
        //name for response field with eventId (to be set as smevent)
        EVENT_ID = 'eventId',
        //name for response field with clientId (to be set as smclient)
        CONTACT_ID = 'contactId';

    protected $response = array();

    /**
     * @var null|bool - response status;
     */
    protected $status = null;

    /**
     * @var null|string - response message;
     */
    protected $message = null;

    /**
     * @var array - additional response fields;
     */
    protected $fields = [];

    /**
     * Response constructor.
     * @param array $data
     * @throws Exception
     */
    public function __construct(
        $data = []
    ) {
        if (!empty($data)) {
            $this->setDataWithSetters($data);
        }
    }

    /**
     * @param array $data;
     *
     * @return $this;
     * @throws Exception;
     */
    public function set($data) {
        $this->setDataWithSetters($data);
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param null|string|array $message
     * @return $this
     */
    public function setMessage($message = null)
    {
        $this->message = is_array($message)
            ? implode(' ', $message)
            : $message;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setField($key, $value)
    {
        $this->fields[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getField($key)
    {
        return isset($this->fields[$key])
            ? $this->fields[$key]
            : null;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields($fields = [])
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
     * @return bool
     */
    public function isSuccess()
    {
        if(isset($this->fields['success'])) {
            return boolval($this->fields['success']);
        }

        return boolval($this->getStatus());
    }
}
