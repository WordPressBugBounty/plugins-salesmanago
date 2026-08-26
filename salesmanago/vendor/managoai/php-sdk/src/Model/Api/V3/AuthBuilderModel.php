<?php

namespace SALESmanago\Model\Api\V3;

use SALESmanago\Entity\Api\V3\Auth\ApiKeyMetaEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\User;

class AuthBuilderModel
{
    /**
     * Return prepared request body for auth create method:
     *
     * @param User $User
     * @param ApiKeyMetaEntityInterface $ApiKeyMetaEntity
     * @return array - request body
     */
    public function getCreate(User $User, ApiKeyMetaEntityInterface $ApiKeyMetaEntity): array {
        return array_merge([
            'email' => $User->getEmail(),
            'password' => $User->getPass()
        ], $ApiKeyMetaEntity->jsonSerialize());
    }

    /**
     * Return prepared request body for auth create method:
     *
     * @param ConfigurationInterface $configuration
     * @return array - request body
     */
    public function getRevoke(ConfigurationInterface $configuration): array
    {
        return ['apiKey' => $configuration->getApiV3Key()];
    }
}