<?php

namespace SALESmanago\Services;

use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;

use SALESmanago\Exception\ExceptionCodeResolver;
use SALESmanago\Factories\ReportFactory;
use SALESmanago\Helper\ConnectionClients\cURLClient;

class RequestService
{
    private $connClient;

    /**
     * @var int max attempts to send requests
     */
    protected $maxAttempts = 2;

    /**
     * @var \Exception
     */
    protected $lastError;

    /**
     * @var int quantity of actual attempts
     */
    private $actualAttempts = 0;

    /**
     * @var string - hash of send data;
     */
    private $hashData = null;

    /**
     * RequestService constructor.
     *
     * @param ConfigurationInterface $conf
     * @throws Exception
     */
    public function __construct(ConfigurationInterface $conf)
    {
        try {
            $this->connClient = new cURLClient();

            $this->connClient->setConfiguration($conf->getRequestClientConf());

            if (empty($conf->getRequestClientConf()->getHeaders())
                || empty($conf->getRequestClientConf()->getHost())
            ) {
                $this->connClient
                    ->setHost($conf->getEndpoint())
                    ->setHeader([
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json;charset=UTF-8'
                    ]);
            }
        } catch (\Exception $e) {
            throw new Exception('Error while setting Connection Client: ' . $e->getMessage(), 401);
        }
    }

    /**
     * @throws Exception
     * @param string $method
     * @param string $uri
     * @param array $data
     * @return Response
     */
    final public function request($method, $uri, $data)
    {
        try {
            ReportFactory::doDebugReport(Configuration::getInstance(), [$method, $uri, $data]);

            $this->connClient
                ->setType($method)
                ->setEndpoint($uri);

            $this->connClient->request($data);

            $jsonResponse = $this->connClient->responseJsonDecode();

            ReportFactory::doDebugReport(Configuration::getInstance(), ['response' => $jsonResponse]);
            return $this->toResponse($jsonResponse);
        } catch (\Exception $e) {
            if ($e->getCode() === 428 && Configuration::getInstance()->getRetryRequestIfTimeout()) {//timeout on SM api
                $this->hashData = $this->makeDataHash($data);
                $this->increaseAttemptsCounterAfterTimeOut($data);

                if ($this->checkExceedingOfRequestAttempts()) {
                    throw new Exception(
                        $e->getMessage(),
                        $e->getCode()
                    );
                }
                $this->connClient->setTimeOut($this->connClient->getTimeOut() + 500);
                return $this->request($method, $uri, $data);
            } else {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * @throws Exception
     * @param Response $Response
     * @return Response
     */
    public function validateResponse($Response)
    {
        if ($Response->isSuccess()) {
            return $Response;
        } else {
            $code = ExceptionCodeResolver::codeFromResponseMessage($Response->getMessage(), 110);
            throw new Exception($Response->getMessage(), $code);
        }
    }

    /**
     * @param Response $Response
     * @param array $conditions - array of booleans;
     * @return Response
     * @throws Exception
     */
    public function validateCustomResponse(Response $Response, $conditions = array())
    {
        $condition = array_merge(array(boolval($Response->isSuccess())), $conditions);

        if (!in_array(false, $condition)) {
            return $Response;
        } else {
            $values = $Response->getField('values');
            if (!empty($values)) {
                $valuesMessage = '';
                foreach ($values as $key => $value) {
                    $value = is_array($value) ? implode(', ', $value) : $value;
                    $valuesMessage.= $key . ': ' . $value . '; ';
                }
            }

            $message = 'RequestService::ValidateCustomResponse - some of conditions failed; SM - ' . $Response->getMessage();
            $message.= !empty($valuesMessage) ? ' Values: ' . $valuesMessage : '';
            $code = ExceptionCodeResolver::codeFromResponseMessage($Response->getMessage(), 100);
            $Response->setMessage($message);
            $Response->setStatus(false);
            throw new Exception($message, $code);
        }
    }

    /**
     * @param array $apiResponse
     * @return Response;
     */
    public function toResponse($apiResponse)
    {
        $Response = new Response();

        if (empty($apiResponse)) {
            return $Response;
        }

        $Response
            ->setStatus($apiResponse['success'])
            ->setMessage($apiResponse['message']);

        unset($apiResponse['success']);
        unset($apiResponse['message']);

        if (!empty($apiResponse)) {
            array_walk($apiResponse, function ($value, $key) use (&$Response) {
                $Response->setField($key, $value);
            });
        }

        return $Response;
    }

    /**
     * Update Configuration e.g. Endpoint after Request service has been constructed
     */
    public function updateConfiguration()
    {
        $Configuration = Configuration::getInstance();
        $this->connClient
            ->setHost($Configuration->getEndpoint());
    }

    /**
     * Checking of max Requests attempts returns true id max attempts reached
     *
     * @return bool
     */
    protected function checkExceedingOfRequestAttempts()
    {
        return $this->actualAttempts >= $this->maxAttempts;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function makeDataHash($data)
    {
        return md5(serialize($data));
    }

    /**
     * @param array $data - data to send
     * @return bool
     */
    protected function compareDataHash($data)
    {
        return ($this->hashData == $this->makeDataHash($data));
    }

    /**
     * @param array $dataToSend
     * @return void
     */
    protected function increaseAttemptsCounterAfterTimeOut($dataToSend) {
        if ($this->compareDataHash($dataToSend)) {
            $this->actualAttempts++;
        } else {
            $this->actualAttempts = 1;
        }
    }
}
