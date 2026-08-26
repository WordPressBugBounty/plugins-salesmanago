<?php

namespace SALESmanago\Entity\Contact;

use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Exception\Exception;

class Properties extends AbstractEntity
{
    const
        PROPERTIES   = 'properties';

    private $properties = [];


    public function __construct($data = [])
    {
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
     * @return array $this->properties;
     */
    public function get(){
        return $this->properties;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     * @throws Exception
     */
    public function setItem($name, $value)
    {
        if (empty($name)) {
            throw new Exception('Passed $name is empty');
        }
        if (is_array($name)) {
            throw new Exception('Passed $name is array');
        }
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return mixed|string
     */
    public function getItem($name){
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        return '';
    }

    /**
     * @param array $param;
     *
     * @return $this;
     * @throws Exception;
     */
    public function setItems($param)
    {
        if(empty($param)) {
            return $this;
        }
        if (!is_array($param)) {
            throw new Exception('Passed argument isn\'t array');
        }
        $this->properties = $param;
        return $this;
    }

    /**
     * @param $param
     * @param bool $override
     * @return $this
     * @throws Exception
     */
    public function appendItems($param, $override = true)
    {
        if (!is_array($param)) {
            throw new Exception('Passed argument isn\'t array');
        }
        if($override) {
            $this->properties = array_merge($this->properties, $param);
        }
        else {
            $this->properties = array_merge($param, $this->properties);
        }
        return $this;
    }

}
