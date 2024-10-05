<?php

namespace SALESmanago\Services\Api\V3;

use SALESmanago\Entity\Api\V3\Auth\ApiKeyMetaEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Api\V3\AuthBuilderModel;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Model\Api\V3\ConfigurationBuilderModel;

class AuthService extends BasicService
{
    const
        REQUEST_METHOD_POST = 'POST',
        API_METHOD_CREATE   = '/v3/auth/create',
        API_METHOD_REVOKE   = '/v3/auth/revoke';

    /**
     * @var AuthBuilderModel
     */
    protected $AuthModel;

    /**
     * @var ConfigurationBuilderModel
     */
    protected $ConfigurationBuilderModel;

    public function __construct(
        ConfigurationInterface $ConfigurationV3,
        RequestClientConfigurationInterface $cUrlClientConf = null
    ) {
        parent::__construct($ConfigurationV3, $cUrlClientConf);

        $this->AuthModel = new AuthBuilderModel();
        $this->ConfigurationBuilderModel = new ConfigurationBuilderModel();
    }

    /**
     * @param User $User
     * @param ApiKeyMetaEntityInterface|null $ApiKeyMetaEntity
     * @return Response
     * @throws ApiV3Exception|Exception
     */
    public function create(
        User $User,
        ApiKeyMetaEntityInterface $ApiKeyMetaEntity = null
    ): Response {
        $data = $this->AuthModel->getCreate($User, $ApiKeyMetaEntity);

        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_CREATE,
            $data
        );

        $this->ConfigurationBuilderModel->setApiKeyToConfiguration($this->configuration, $response);

        return new Response([
            'status' => true,
            'message' => '',
            'fields' => ['conf' => $this->configuration]
        ]);
    }

    /**
     * Revoke api key in $this->configuration
     *
     * @return Response
     * @throws ApiV3Exception|Exception
     */
    public function revoke(): Response
    {
        $data = $this->AuthModel->getRevoke($this->configuration);

        try {
            $response = $this->RequestService->request(
                self::REQUEST_METHOD_POST,
                self::API_METHOD_REVOKE,
                $data
            );
        } catch (ApiV3Exception $exception) {
                //empty API KEY v3 and couldn't auth revoke method, it's fine if API KEY v3 never added to configuration:
            if (($exception->getMessage() == '[Specified json is not valid]' && $exception->getCode() == 400)
                //when api key v3 revoked earlier (in case manual add/edit api key):
                || (is_array($exception->getCodes()) && in_array(10, $exception->getCodes()))
            ) {
                //all fine:
                $response = [];
            } else {
                throw $exception;
            }
        }


        if (!empty($response['apiKeyName'])) {//on success return apiKeyName
            $this->configuration->setApiV3Key(null);
        }

        return new Response([
            'status' => true,
            'message' => '',
            'fields' => $response
        ]);
    }
}
