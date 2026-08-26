<?php

namespace SALESmanago\Services;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\UserModel;
use SALESmanago\Model\ConfModel;
use SALESmanago\Entity\Configuration;

/**
 * Class UserAccountService - implements functionality with salesmanago user/owner account;
 * @package SALESmanago\Services
 */
class UserAccountService
{
    const
        REQUEST_METHOD_POST = 'POST',
        METHOD_LOGIN_AUTHORIZE = '/api/authorization/token',
        METHOD_ACCOUNT_INTEGRATION = '/api/account/integration',
        METHOD_LIST_USERS = '/api/user/listByClient',
        METHOD_REDIRECT_TO_APP = '/api/authorization/authorize?t=',
        METHOD_REFRESH_TOKEN = '/api/authorization/refreshToken';

    /**
     * @var ConfigurationInterface - integration configuration
     */
    protected $conf;

    /**
     * @var UserModel
     */
    protected $UserModel;

    /**
     * @var ConfModel
     */
    protected $ConfModel;

    /**
     * @var RequestService
     */
    protected $RequestService;

    /**
     * UserAccountService constructor.
     *
     * @param ConfigurationInterface $conf
     * @throws Exception
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->UserModel = new UserModel();
        $this->ConfModel = new ConfModel(Configuration::setInstance($conf));
        $this->RequestService = new RequestService(Configuration::getInstance());
    }

    /**
     * @param User $User
     * @return Response
     * @throws Exception
     */
    public function login(User $User)
    {
        $responseAccountAuthorize = $this->accountAuthorize($User);

        //set necessary data to conf;
        $this->conf = $this->ConfModel->setConfAfterAccountAuthorization($responseAccountAuthorize, $User);

        $responseIntegration = $this->accountIntegrationSettings();

        //set necessary data to conf;
        $this->conf = $this->ConfModel->setConfAfterIntegration($responseIntegration);

        $responseCheckIfAccountActive = $this->checkIfAccountIsActive();

        $this->conf = $this->ConfModel->setOwnersListToConf($responseCheckIfAccountActive);

        $this->conf->setActive(true);

        return new Response([
            'status' => true,
            'message' => '',
            'fields' => ['conf' => $this->conf]
        ]);
    }

    /**
     * @param User $User
     * @return Response
     * @throws Exception
     */
    protected function accountAuthorize(User $User)
    {
        $data = $this->UserModel->getUserForAuthorization($User);

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::METHOD_LOGIN_AUTHORIZE,
            $data
        );

        return $this->RequestService->validateCustomResponse(
            $Response,
            [
                boolval($Response->getField(Configuration::TOKEN))
            ]
        );
    }

    /**
     * @throws Exception
     * @var ConfigurationInterface $conf
     * @return Response
     */
    protected function accountIntegrationSettings()
    {
        $this->RequestService = new RequestService($this->conf);

        $data = [
            Configuration::TOKEN   => $this->conf->getToken(),
            Configuration::API_KEY => $this->conf->getApiKey(),
        ];

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::METHOD_ACCOUNT_INTEGRATION,
            $data
        );

        return $this->RequestService->validateCustomResponse(
            $Response,
            [
                boolval($Response->getField(User::SHORT_ID))
            ]
        );
    }

    /**
     * @return Response
     * @throws Exception
     */
    protected function checkIfAccountIsActive()
    {
        $Response = $this->RequestService
            ->request(
                self::REQUEST_METHOD_POST,
                self::METHOD_LIST_USERS,
                $this->ConfModel->getAuthorizationApiDataWithOwner()
            );

        return $this->RequestService->validateResponse($Response);
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function refreshToken()
    {
        try {
            $Response = $this->RequestService
                ->request(
                    self::REQUEST_METHOD_POST,
                    self::METHOD_REFRESH_TOKEN,
                    $this->ConfModel->getAuthorizationApiDataWithOwner()
                );
            return $this->RequestService->validateResponse($Response);
        } catch (\Exception $e) {
            throw new Exception('Inactive account', 40);
        }
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function getOwnersList()
    {
        $Response = $this->RequestService->request(
                self::REQUEST_METHOD_POST,
                self::METHOD_LIST_USERS,
                $this->ConfModel->getAuthorizationApiDataWithOwner()
            );
        return $this->RequestService->validateCustomResponse(
            $Response,
            [
                boolval(!empty($Response->getField('users')))
            ]
        );
    }
}