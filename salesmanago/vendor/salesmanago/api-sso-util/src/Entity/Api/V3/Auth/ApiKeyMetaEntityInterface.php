<?php

namespace SALESmanago\Entity\Api\V3\Auth;

use \JsonSerializable;

interface ApiKeyMetaEntityInterface extends JsonSerializable
{

    public const
            //expiry for api key:
            NEVER        = 'NEVER',
            SEVEN_DAYS   = 'SEVEN_DAYS',
            ONE_MONTH    = 'ONE_MONTH',
            THREE_MONTHS = 'THREE_MONTHS',
            ONE_YEAR     = 'ONE_YEAR';

    public function getKeyName();

    /**
     * @param string $keyName
     * @return ApiKeyMetaEntityInterface
     */
    public function setKeyName(string $keyName): ApiKeyMetaEntityInterface;

    /**
     * @return string
     */
    public function getExpiry(): string;

    /**
     * @param string $expiry
     * @return ApiKeyMetaEntityInterface
     */
    public function setExpiry(string $expiry): ApiKeyMetaEntityInterface;

    /**
     * @return string|null
     */
    public function getEndpoint(): ?string;

    /**
     * @param string $endpoint
     * @return ApiKeyMetaEntityInterface
     */
    public function setEndpoint(string $endpoint): ApiKeyMetaEntityInterface;

    /**
     * @return string|null
     */
    public function getWebhookUrl(): ?string;

    /**
     * @param string $webhookUrl
     * @return ApiKeyMetaEntityInterface
     */
    public function setWebhookUrl(string $webhookUrl): ApiKeyMetaEntityInterface;
}