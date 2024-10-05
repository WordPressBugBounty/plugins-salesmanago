<?php


namespace SALESmanago\Helper\ConnectionClients;

use PHPUnit\Framework\TestCase;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\Api\V3\AuthService;

class cURLClient
{
    const
        DEFAULT_TIME_OUT  = 30000,//@deprecated
        TIMEOUT           = 30,
        TIMEOUT_MS        = 30000,
        CONNECTTIMEOUT_MS = 30000,
        REQUEST_TYPE_POST = 'POST',
        REQUEST_TYPE_GET  = 'GET';

    /**
     * @var string - request type (GET, PUT, POST, DELETE, etc.);
     */
    protected $type = 'POST';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $host = null;

    /**
     * @var string
     */
    protected $endpoint = '';

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
     * @var null|bool|string - request response;
     */
    protected $response = null;

    /**
     * @var array - array of header => value
     */
    protected $headers = [];

    /**
     * @var int - The number of milliseconds to wait while trying to connect.
     *            Use 0 to wait indefinitely. If libcurl is built to use the standard system
     *            name resolver, that portion of the connect will still use full-second resolution
     *            for timeouts with a minimum timeout allowed of one second.
     */
    protected $connectTimeOutMs = 30000;

    /**
     * @param string $param - request type (GET, PUT, POST, DELETE, etc.)
     * @return $this
     */
    public function setType($param)
    {
        $this->type = strtoupper($param);
        return $this;
    }

    /**
     * @deprecated since v3.1.1
     * @see cURLClient->setConfiguration()
     *
     * @param string $param
     */
    public function setUrl($param)
    {
        $this->url = $param;
        return $this;
    }

    /**
     * @deprecated since v3.1.1
     * @see cURLClient->setConfiguration()
     *
     * @param string $param
     */
    public function setHost($param)
    {
        $this->host = $param;
        return $this;
    }

    /**
     * @deprecated since v3.1.1
     * @see cURLClient->setConfiguration()
     *
     * @param int $param
     * @return $this
     */
    public function setTimeOut($param)
    {
        $this->timeOutMs = $param;
        return $this;
    }

    /**
     * @deprecated since v3.1.1
     * @see cURLClient->setConfiguration()
     *
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOutMs;
    }

    /**
     * @deprecated since v3.1.1
     * @see cURLClient->setConfiguration()
     * @see RequestClientConfigurationInterface
     *
     * @param string $param
     * @return cURLClient
     */
    public function setEndpoint($param)
    {
        $this->endpoint = $param;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @deprecated since v3.1.1
     * @see cURLClient->setConfiguration()
     * @see RequestClientConfigurationInterface
     *
     * @param array $param
     */
    public function setHeader($param)
    {
        $this->headers = array_merge($this->headers, $param);
        return $this;
    }

    /**
     *
     */
    private function buildHeaders()
    {
        $preparedHeader = [];
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $preparedHeader = array_merge($preparedHeader, [$name.': '. $value]);
            }
        }

        return $preparedHeader;
    }

    /**
     * Reset headers
     * @return $this
     */
    public function resetHeaders()
    {
        $this->headers = [];
        return $this;
    }

    /**
     * Decode json response
     * @return array
     */
    public function responseJsonDecode()
    {
        return json_decode($this->response, true);
    }

    /**
     * Send data with curl oneway;
     * @param array|null $data
     * @param bool $toJson
     * @throws Exception
     */
    public function request($data = null, $toJson = true)
    {
        if ($data !== null) {
            $data = $toJson ? json_encode($data, JSON_INVALID_UTF8_IGNORE) : $data;
        }

        if (empty($this->headers)
            || $this->type === self::REQUEST_TYPE_POST
        ) {
            if ($data !== null) {
                $this->headers['Content-Length'] = strlen($data);
            }
        }

        $this->headers['Content-Type'] = 'application/json';

        $this->timeOut = (empty($this->timeOut)) ? self::TIMEOUT : $this->timeOut;
        $this->timeOutMs = (empty($this->timeOutMs)) ? self::TIMEOUT_MS : $this->timeOutMs;
        $this->connectTimeOutMs = (empty($this->connectTimeOutMs)) ? self::CONNECTTIMEOUT_MS : $this->connectTimeOutMs;

        $url = empty($this->host) ? $this->url : $this->host . $this->endpoint;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->type);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeaders());
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeOutMs);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeOutMs);

        $this->response = curl_exec($ch);

        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        $this->throwErrorIfExist($error, $errno);
    }

    /**
     * @param $errMessage
     * @param $errNumber
     * @throws Exception
     * @return bool
     */
    private function throwErrorIfExist($errMessage, $errNumber)
    {
        if (!empty($errNumber)) {
            $smErrNumber = ($errNumber < 10) ? '40'.$errNumber : '4'.$errNumber;
            $message = 'SALESmanago cURL error [code:' . $smErrNumber . ']: ' . $errMessage;

            throw new Exception($message, intval($smErrNumber));
        }

        return false;
    }

    /**
     * @param RequestClientConfigurationInterface $Conf
     * @return self
     */
    public function setConfiguration(RequestClientConfigurationInterface $Conf)
    {
        $this->timeOut          = $Conf->getTimeOut();
        $this->timeOutMs        = $Conf->getTimeOutMs();
        $this->connectTimeOutMs = $Conf->getConnectTimeOutMs();
        $this->endpoint         = $Conf->getEndpoint();
        $this->host             = $Conf->getHost();
        $this->headers          = $Conf->getHeaders();

        if (!empty($Conf->getUrl())) {
            $this->url = $Conf->getUrl();
        }

        return $this;
    }
}
