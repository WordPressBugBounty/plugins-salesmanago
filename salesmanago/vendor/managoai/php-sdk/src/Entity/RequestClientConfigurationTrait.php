<?php

namespace SALESmanago\Entity;

trait RequestClientConfigurationTrait
{
    public function __construct(
        $host = '',
        $endpoint = '',
        $url = '',
        $headers = [],
        $timeOut = 30,
        $timeOutMs = 30000,
        $connectTimeOutMs = 30000
    ) {
        $this->timeOut = $timeOut;
        $this->timeOutMs = $timeOutMs;
        $this->connectTimeOutMs = $connectTimeOutMs;
        $this->headers = $headers;
    }

    /**
     * @var int - The maximum number of seconds to allow cURL functions to execute.
     */
    private $timeOut;

    /**
     * @var int - The maximum number of milliseconds to allow cURL functions to execute.
     *            If libcurl is built to use the standard system name resolver, that portion
     *            of the connect will still use full-second resolution for timeouts with a minimum
     *            timeout allowed of one second.
     */
    private $timeOutMs;

    /**
     * @var int - The number of milliseconds to wait while trying to connect.
     *            Use 0 to wait indefinitely. If libcurl is built to use the standard system
     *            name resolver, that portion of the connect will still use full-second resolution
     *            for timeouts with a minimum timeout allowed of one second.
     */
    private $connectTimeOutMs;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $host;

    /**
     * @var array - array of header => value
     */
    private $headers;

    /**
     * @var string - request type (GET, PUT, POST, DELETE, etc.);
     */
    private $url;

    public function setTimeOut($param)
    {
        $this->timeOut = $param;
        return $this;
    }

    public function getTimeOut()
    {
        return $this->timeOut;
    }

    public function setTimeOutMs($param)
    {
        $this->timeOutMs = $param;
        return $this;
    }

    public function getTimeOutMs()
    {
        return $this->timeOutMs;
    }

    public function setConnectTimeOutMs($param)
    {
        $this->connectTimeOutMs = $param;
        return $this;
    }

    public function getConnectTimeOutMs()
    {
        return $this->connectTimeOutMs;
    }

    public function setEndpoint($param)
    {
        $this->endpoint = $param;
        return $this;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setHeaders($array)
    {
        $this->headers = $array;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setUrl($string)
    {
        $this->url = $string;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setHost($string)
    {
        $this->host = $string;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }
}