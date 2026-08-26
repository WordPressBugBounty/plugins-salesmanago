<?php

namespace SALESmanago\Entity;

interface RequestClientConfigurationInterface
{
    /**
     * @param int $param
     * @return mixed
     */
    public function setTimeOut($param);

    /**
     * @return int
     */
    public function getTimeOut();

    /**
     * @param int $param
     * @return mixed
     */
    public function setTimeOutMs($param);

    /**
     * @return int
     */
    public function getTimeOutMs();

    /**
     * @param int $param
     * @return mixed
     */
    public function setConnectTimeOutMs($param);

    /**
     * @return int
     */
    public function getConnectTimeOutMs();

    /**
     * @param string $param
     * @return self
     */
    public function setEndpoint($param);

    /**
     * @return string
     */
    public function getEndpoint();

    /**
     * @param array $array
     * @return self
     */
    public function setHeaders($array);

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param string $string
     * @return self
     */
    public function setUrl($string);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $string
     * @return self
     */
    public function setHost($string);

    /**
     * @return string
     */
    public function getHost();
}