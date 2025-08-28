<?php


namespace SALESmanago\Controller;

use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Services\UserAccountService;

use SALESmanago\Exception\Exception;

class UserController
{
    /**
     * @var Configuration
     */
    protected $conf;

    /**
     * @var UserAccountService
     */
    protected $service;

    /**
     * UserController constructor.
     * @param ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        Configuration::setInstance($conf);
        $this->conf = $conf;
        $this->service = new UserAccountService($conf);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getOwnersList()
    {
        $Response = $this->service->getOwnersList();
        return $Response->getField('users');
    }
}