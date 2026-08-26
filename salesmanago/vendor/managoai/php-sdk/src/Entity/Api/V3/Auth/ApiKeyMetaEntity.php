<?php

namespace SALESmanago\Entity\Api\V3\Auth;

use SALESmanago\Entity\AbstractEntity;

class ApiKeyMetaEntity extends AbstractEntity implements ApiKeyMetaEntityInterface
{
    /**
     * @var string
     */
    protected $keyName;

    /**
     * @var string - SEVEN_DAYS, ONE_MONTH, THREE_MONTHS, ONE_YEAR, NEVER, ...
     * @see ApiKeyMetaEntity CONSTs
     */
    protected $expiry;

    /**
     * @var string - app endpoint. Required only if user got multiple accounts with same email on different apps
     */
    protected $endpoint;

    /**
     * @var string - webhook url
     */
    private $webhookUrl;

    public function __construct()
    {
        $this->keyName = 'CREATED_' . time();
        $this->expiry  = ApiKeyMetaEntityInterface::NEVER;
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param string|null $keyName
     * @return ApiKeyMetaEntity
     */
    public function setKeyName(?string $keyName): ApiKeyMetaEntityInterface
    {
        $this->keyName = $keyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpiry(): string
    {
        return $this->expiry;
    }

    /**
     * @param string $expiry
     * @return ApiKeyMetaEntity
     */
    public function setExpiry(string $expiry): ApiKeyMetaEntityInterface
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     * @return ApiKeyMetaEntityInterface
     */
    public function setEndpoint(string $endpoint): ApiKeyMetaEntityInterface
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     *  Set webhook url
     *
     * @param string $webhookUrl
     * @return ApiKeyMetaEntityInterface
     */
    public function setWebhookUrl(string $webhookUrl): ApiKeyMetaEntityInterface
    {
        $this->webhookUrl = $webhookUrl;
        return $this;
    }

    /**
     * Get webhook url
     *
     * @return string|null
     */
    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $apiKeyRequest = [
            'keyName' => $this->keyName,
            'expiry'  => $this->expiry
        ];

        if (!empty($this->webhookUrl)) {
            $apiKeyRequest['webhookUrl'] = $this->webhookUrl;
        }

        if (!empty($this->endpoint)) {
            $apiKeyRequest['endpoint'] = $this->endpoint;
        }

        return $apiKeyRequest;
    }
}