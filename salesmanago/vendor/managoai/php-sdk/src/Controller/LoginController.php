<?php

namespace SALESmanago\Controller;


use SALESmanago\Controller\User\Vendor\AccountController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\UnionConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Model\Report\ReportModel;
use SALESmanago\Services\UserAccountService;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\Report\ReportService;
use SALESmanago\Services\Api\V3\AuthService;

/**
 * Class LoginController
 * @deprecated since v3.1.7 - logic must go to more general controller
 * @see AccountController
 */
class LoginController
{
    /**
     * @var UnionConfigurationInterface
     */
    protected $conf;

    /**
     * @var UserAccountService
     */
    protected $service;

    /**
     * @var AuthService
     */
    protected $apiV3AuthService;

    /**
     * LoginController constructor.
     * @param Configuration $conf
     * @throws Exception
     */
    public function __construct(ConfigurationInterface $conf)
    {
        Configuration::setInstance($conf);

        $this->conf    = $conf;
        $this->service = new UserAccountService($this->conf);
    }

    /**
     * @param User $User
     * @throws Exception
     * @return Response
     */
    public function login(User $User) {
        $loginResponse = $this->service->login($User);

        try {
            ReportService::getInstance($this->conf)
                ->reportAction(ReportModel::ACT_LOGIN);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $loginResponse;
    }
}
