<?php

namespace SALESmanago\Controller\User\Vendor;

use SALESmanago\Adapter\ConfigurationStoreAdapterInterface;
use SALESmanago\Entity\Api\V3\Auth\ApiKeyMetaEntity;
use SALESmanago\Entity\Api\V3\Auth\ApiKeyMetaEntityInterface;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Response;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Entity\UnionConfigurationInterface;
use SALESmanago\Entity\User;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Report\ReportModel;
use SALESmanago\Services\Api\V3\AuthService;
use SALESmanago\Services\Report\ReportService;
use SALESmanago\Services\UserAccountService;

class AccountController
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
     * @var ConfigurationStoreAdapterInterface
     */
    protected $ConfigurationStoreAdapter;

    /**
     * LoginController constructor.
     *
     * @param UnionConfigurationInterface $conf
     * @param ConfigurationStoreAdapterInterface|null $ConfigurationStoreAdapter
     * @throws Exception
     */
    public function __construct(
        UnionConfigurationInterface $conf,
        ?ConfigurationStoreAdapterInterface $ConfigurationStoreAdapter
    ) {
        UnionConfigurationEntity::setInstance($conf);

        $this->conf                      = $conf;
        $this->ConfigurationStoreAdapter = $ConfigurationStoreAdapter;
        $this->service                   = new UserAccountService($this->conf);
    }

    /**
     * Login to account
     *
     * @param User $User
     * @param ApiKeyMetaEntityInterface|null $ApiKeyMetaEntity
     * @param callable|null $callback (ApiKeyMetaEntityInterface $ApiKeyMetaEntity, Configuration $conf, Response $Response)
     * @return Response
     * @throws ApiV3Exception|Exception
     */
    public function login(
        User $User,
        ?ApiKeyMetaEntityInterface $ApiKeyMetaEntity = null,
        ?array $callback = null
    )  {
        $Response = $this->service->login($User);

        //this will keep backward compatibility:
        $ApiKeyMetaEntity = $ApiKeyMetaEntity ?? new ApiKeyMetaEntity();

        $ApiKeyMetaEntity
            ->setKeyName($this->conf->getPlatformName() . '_' . time())
            ->setEndpoint(str_replace('https://', '', $this->conf->getEndpoint()));

        //here we can modify ApiKeyMetaEntity & Configuration:
        if ($callback !== null) {
            $method = $callback[1];
            $callback[0]->$method($ApiKeyMetaEntity, $this->conf, $Response);
        }

        (new AuthService($this->conf))
            ->create(
                $User,
                $ApiKeyMetaEntity
            );

        try {
            if (!empty($this->ConfigurationStoreAdapter)) {
                $this->ConfigurationStoreAdapter->storeUnionConfiguration($this->conf);
            }

            ReportService::getInstance($this->conf)
                ->reportAction(ReportModel::ACT_LOGIN);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $Response;
    }

    /**
     * Logout from account
     *
     * @return Response|null
     * @throws ApiV3Exception|Exception
     */
    public function logout()
    {
        $Response = (new AuthService($this->conf))->revoke();

        if (!empty($this->ConfigurationStoreAdapter)) {
            $this->ConfigurationStoreAdapter->removeConfiguration();
            $this->ConfigurationStoreAdapter->removeAuthConfiguration();
        }

        ReportService::getInstance($this->conf)
            ->reportAction(ReportModel::ACT_LOGOUT);

        return $Response;
    }
}
