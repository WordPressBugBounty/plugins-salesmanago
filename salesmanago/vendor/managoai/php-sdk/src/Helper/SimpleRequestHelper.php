<?php


namespace SALESmanago\Helper;


use SALESmanago\Exception\Exception;

/**
 * Class SimpleRequestHelper
 * @package SALESmanago\Helper
 */
class SimpleRequestHelper
{
    /**
     * @var string - request type (GET, PUT, POST, DELETE, etc.);
     */
    protected $type = 'POST';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $timeOut = 3000;

    /**
     * @var null - request response;
     */
    protected $response = null;

    /**
     * @param string $param
     * @return $this
     */
    public function setType($param)
    {
        $this->type = strtoupper($param);
        return $this;
    }

    /**
     * @param $param
     * @return bool
     */
    public function setTimeOut($param)
    {
        $this->timeOut = $param;
        return true;
    }

    /**
     * @param string $param
     */
    public function setUrl($param)
    {
        $this->url = $param;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Send data with curl oneway;
     * @param $data
     * @param bool $toJson
     */
    public function curlCall($data, $toJson = true)
    {
        $data               = $toJson ? json_encode($data) : $data;
        $connection_timeout = $this->timeOut;
        $url                = $this->url;
        $ch                 = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (isset($connection_timeout)
            && !empty($connection_timeout)
        ) {
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $connection_timeout);
        }
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        $this->response = curl_exec($ch);
        curl_close($ch);
    }

}