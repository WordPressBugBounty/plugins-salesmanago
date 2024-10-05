<?php

namespace SALESmanago\Entity;

use SALESmanago\Exception\Exception;

class cUrlClientConfiguration extends AbstractEntity implements RequestClientConfigurationInterface
{

    const
        TIMEOUT            = 'timeout',
        TIMEOUT_MS         = 'timeOutMs',
        CONNECT_TIMEOUT_MS = 'connectTimeOutMs',
        HOST               = 'host',
        URL                = 'url';

    /**
     * @var int - The maximum number of seconds to allow cURL functions to execute.
     */
    protected $timeOut = 30;

    /**
     * @var int - The maximum number of milliseconds to allow cURL functions to execute.
     *            If libcurl is built to use the standard system name resolver, that portion
     *            of the connect will still use full-second resolution for timeouts with a minimum
     *            timeout allowed of one second.
     */
    protected $timeOutMs = 30000;

    /**
     * @var int - The number of milliseconds to wait while trying to connect.
     *            Use 0 to wait indefinitely. If libcurl is built to use the standard system
     *            name resolver, that portion of the connect will still use full-second resolution
     *            for timeouts with a minimum timeout allowed of one second.
     */
    protected $connectTimeOutMs = 30000;

    /**
     * @var string endpoint
     */
    protected $endpoint = '';

    /**
     * @var array
     */
    protected $headers = [
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json;charset=UTF-8'
    ];

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @param array|null $data
     * @throws Exception
     */
    public function __construct($data = null)
    {
        if (!empty($data)) {
            $this->setDataWithSetters($data);
        }
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

    /**
     * @param int $param
     * @return $this
     */
    public function setTimeOut($param)
    {
        $this->timeOut = $param;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * @param int $param
     * @return $this
     */
    public function setTimeOutMs($param)
    {
        $this->timeOutMs = $param;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeOutMs()
    {
        return $this->timeOutMs;
    }

    /**
     * @param int $param
     * @return $this
     */
    public function setConnectTimeOutMs($param)
    {
        $this->connectTimeOutMs = $param;
        return $this;
    }

    /**
     * @return int
     */
    public function getConnectTimeOutMs()
    {
        return $this->connectTimeOutMs;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setEndpoint($param)
    {
        $this->endpoint = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param array $array
     * @return $this|RequestClientConfigurationInterface
     */
    public function setHeaders($array)
    {
        $this->headers = $array;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $string
     */
    public function setUrl($string)
    {
        $this->url = $string;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $string
     */
    public function setHost($string)
    {
        $this->host = $string;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
}
