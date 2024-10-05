<?php

namespace SALESmanago\Entity\Contact;

use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Exception\Exception;

class Coupon extends AbstractEntity
{
    const
        NAME   = 'name',
        LENGTH = 'length',
        VALID  = 'valid',
        COUPON = 'coupon';

    /**
     * @var string - max length 64 - coupon name
     */
    private $name;

    /**
     * @var int - length of coupon in case of automatic generation (the value from 5 to 64)
     */
    private $length;

    /**
     * @var int - expiration date of coupon (timestamp in miliseconds), by default: year and a day
     */
    private $valid;

    /**
     * @var string - Max. length 32 value of coupon in case of manual input (5 characters minimum)
     */
    private $coupon;

    /**
     * @param array|null $data
     * @throws Exception
     */
    public function __construct($data = null)
    {
        $this
            ->setName('DEFAULT_COUPON')
            ->setValid(time()+(60*60*24*360));

        if ($data != null && is_array($data)) {
            $this->set($data);
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * @param int $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @param mixed $coupon
     */
    public function setCoupon($coupon)
    {
        $this->coupon = $coupon;
        return $this;
    }
}