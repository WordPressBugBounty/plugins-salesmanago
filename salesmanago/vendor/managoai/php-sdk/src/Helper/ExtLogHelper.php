<?php

namespace SALESmanago\Helper;

use SALESmanago\Entity\RequestClientConfiguration;
use SALESmanago\Exception\Exception;
use SALESmanago\Helper\ConnectionClients\cURLClient;

class ExtLogHelper
{
    private static $instance = null;

    protected $connectClient;

    private function __construct()
    {
        $this->connectClient = new cURLClient();

        $this->connectClient
            ->setConfiguration((new RequestClientConfiguration())
                ->setHost('https://survey.salesmanago.com')
                ->setEndpoint('/2.0/fetch')
            )->setType(cURLClient::REQUEST_TYPE_POST);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Log data to external server
     *
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function logData(array $data): void
    {
        $this->connectClient->request($data);
    }
}