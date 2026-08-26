<?php

namespace SALESmanago\Entity\Contact;

use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Exception\Exception;
use SALESmanago\Helper\EntityDataHelper;

class Address extends AbstractEntity
{
    const
        ADDRESS   = 'address',
        STREET_AD = 'streetAddress',
        ZIP_CODE  = 'zipCode',
        CITY      = 'city',
        COUNTRY   = 'country',
        PROVINCE  = 'province';

    /**
     * @var string
     */
    private $streetAddress;

    /**
     * @var string
     */
    private $zipCode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $province;

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
     * @param mixed $param  - array || string
     * @return $this
     */
    public function setStreetAddress($param)
    {
        $this->streetAddress = is_array($param)
            ? EntityDataHelper::setStrFromArr($param)
            : $param;
        return $this;
    }

    /**
     * @return string $this->streetAddress;
     */
    public function getStreetAddress(){
        return $this->streetAddress;
    }

    /**
     * @param string $param - zipcode
     * @return $this
     */
    public function setZipCode($param)
    {
        $this->zipCode = $param;
        return $this;
    }

    /**
     * @return string $this->zipCode;
     */
    public function getZipCode(){
        return $this->zipCode;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setCity($param)
    {
        $this->city = $param;
        return $this;
    }

    /**
     * @return string $this->city;
     */
    public function getCity(){
        return $this->city;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setCountry($param)
    {
        $this->country = $param;
        return $this;
    }

    /**
     * @return string $this->country
     */
    public function getCountry(){
        return $this->country;
    }

    /**
     * @param string $param
     * @return $this;
     */
    public function setProvince($param){
        $this->province = $param;
        return $this;
    }

    /**
     * @return string - $this->province;
     */
    public function getProvince(){
        return $this->province;
    }
}
