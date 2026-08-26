<?php

namespace SALESmanago\Services;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\LoyaltyProgram;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\ConfModel;
use SALESmanago\Model\LoyaltyProgramModel;

class LoyaltyProgramService
{
    const
        ADD_CONTACT         = '/api/loyalty/program/v1/addContact',
        MODIFY_POINTS       = '/api/loyalty/program/v1/modifyPoints',
        REMOVE_CONTACT      = '/api/loyalty/program/v1/removeContact',
        CONTACT_LOYALTY     = 'contacts',
        REQUEST_METHOD_POST = 'POST';

    private $RequestService;
    private $conf;
    private $ConfModel;

    /**
     * LoyaltyProgramService constructor.
     *
     * @param ConfigurationInterface $conf
     * @throws Exception
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf           = $conf;
        $this->ConfModel      = new ConfModel($conf);
        $this->RequestService = new RequestService($conf);
    }

    /**
     * @param LoyaltyProgram $LoyaltyProgram
     * @return Response
     * @throws Exception
     */
    public function add(LoyaltyProgram $LoyaltyProgram)
    {
        $LoyaltyProgramModel = new LoyaltyProgramModel($LoyaltyProgram);

        $settings = $this->ConfModel->getAuthorizationApiDataWithOwner();
        $dataForRequest = $LoyaltyProgramModel->getDataForRequest();

        $data = array_merge(
            $settings,
            [LoyaltyProgramModel::LOYALTY_PROGRAM_NAME => $LoyaltyProgram->getLoyaltyProgramName()],
            [self::CONTACT_LOYALTY => $dataForRequest]
        );

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::ADD_CONTACT,
            $data
        );
        return $this->RequestService->validateResponse($Response);
    }

    /**
     * @param LoyaltyProgram $LoyaltyProgram
     * @return Response
     * @throws Exception
     */
    public function remove(LoyaltyProgram $LoyaltyProgram)
    {
        $LoyaltyProgramModel = new LoyaltyProgramModel($LoyaltyProgram);

        $settings = $this->ConfModel->getAuthorizationApiDataWithOwner();
        $dataForRequest = $LoyaltyProgramModel->getDataForRequest();

        $data = array_merge(
            $settings,
            [LoyaltyProgramModel::LOYALTY_PROGRAM_NAME => $LoyaltyProgram->getLoyaltyProgramName()],
            [self::CONTACT_LOYALTY => $dataForRequest]
        );

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::REMOVE_CONTACT,
            $data
        );
        return $this->RequestService->validateResponse($Response);
    }

    /**
     * @param LoyaltyProgram $LoyaltyProgram
     * @return Response
     * @throws Exception
     */
    public function modifyPoints(LoyaltyProgram $LoyaltyProgram)
    {
        $LoyaltyProgramModel = new LoyaltyProgramModel($LoyaltyProgram);

        $settings = $this->ConfModel->getAuthorizationApiDataWithOwner();
        $dataForRequest = $LoyaltyProgramModel->getDataForRequest();
        $dataForModifyRequest = $LoyaltyProgramModel->getDataForModifyRequest();

        $data = array_merge(
            $settings,
            [LoyaltyProgramModel::LOYALTY_PROGRAM_NAME => $LoyaltyProgram->getLoyaltyProgramName()],
            $dataForModifyRequest,
            [self::CONTACT_LOYALTY => $dataForRequest]
        );

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::MODIFY_POINTS,
            $data
        );
        return $this->RequestService->validateResponse($Response);
    }
}