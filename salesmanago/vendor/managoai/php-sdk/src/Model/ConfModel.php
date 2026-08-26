<?php


namespace SALESmanago\Model;

use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Entity\User;

class ConfModel
{
    const
        REQUEST_TIME = 'requestTime';

    /**
     * @var ConfigurationInterface
     */
    private $conf;

    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
    }

    /**
     * @return array
     */
    public function getAuthorizationApiData()
    {
        return [
            Configuration::CLIENT_ID => $this->conf->getClientId(),
            Configuration::API_KEY   => $this->conf->getApiKey(),
            Configuration::SHA       => $this->conf->getSha(),
            self::REQUEST_TIME  => time()
        ];
    }

    /**
     * @return array
     */
    public function getAuthorizationApiDataWithOwner()
    {
        return array_merge(
            $this->getAuthorizationApiData(),
            [Configuration::OWNER => $this->conf->getOwner()]
        );
    }

    /**
     * @param Response $ResponseAccountAuthorize
     * @param User $User
     * @return ConfigurationInterface
     */
    public function setConfAfterAccountAuthorization(Response $ResponseAccountAuthorize, User $User)
    {
        $this->conf
            ->setOwner($User->getEmail())
            ->setToken($ResponseAccountAuthorize->getField(Configuration::TOKEN))
            ->setEndpoint($ResponseAccountAuthorize->getField(Configuration::ENDPOINT))
            ->setSmApp($ResponseAccountAuthorize->getField(Configuration::SM_APP));

        return $this->conf;
    }

    /**
     * @param Response $ResponseIntegration
     * @return ConfigurationInterface
     */
    public function setConfAfterIntegration($ResponseIntegration) {
        $this->conf
            ->setSha($ResponseIntegration->getField(User::SHA1))
            ->setClientId($ResponseIntegration->getField(User::SHORT_ID));

        return $this->conf;
    }

    /**
     * @param Response $Response
     * @return ConfigurationInterface
     */
    public function setOwnersListToConf(Response $Response)
    {
        return $this->conf
            ->setOwnersList($Response->getField('users'));
    }
}