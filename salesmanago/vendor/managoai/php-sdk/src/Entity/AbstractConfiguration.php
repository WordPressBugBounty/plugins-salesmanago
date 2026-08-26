<?php

namespace SALESmanago\Entity;

use SALESmanago\Exception\Exception;

class AbstractConfiguration extends AbstractEntity
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var bool
     */
    private $retryRequestIfTimeout = false;

    final protected function __construct() {}
    final protected function __clone() {}

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    /**
     * @return mixed|static
     */
    public static function getInstance()
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     * @param ConfigurationInterface $class
     * @return mixed
     */
    public static function setInstance(ConfigurationInterface $class)
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = $class;
        }

        return self::$instances[$cls];
    }

    /**
     * Sets data from array
     * @param array $data
     * @return $this;
     * @throws Exception
     */
    public function set($data)
    {
        $this->setDataWithSetters($data);
        return $this;
    }
}