<?php


namespace SALESmanago\Entity;


interface ConfigurationInterface
{
    /**
     * @return boolean
     */
    function isActive();

    /**
     * @param boolean $active
     * @return $this
     */
    function setActive($active);

    /**
     * @return string
     */
    function getEndpoint();

    /**
     * @param string $endpoint
     * @param boolean $ssl
     * @return $this
     */
    function setEndpoint($endpoint, $ssl = true);

    /**
     * @return string
     */
    function getClientId();

    /**
     * @param string $clientId
     * @return $this
     */
    function setClientId($clientId);

    /**
     * @return string
     */
    function getApiKey();

    /**
     * @param string $apiKey
     * @return $this
     */
    function setApiKey($apiKey);

    /**
     * @return $this
     */
    function setDefaultApiKey();

    /**
     * @return string
     */
    function getApiSecret();

    /**
     * @param string $apiSecret
     * @return $this
     */
    function setApiSecret($apiSecret);

    /**
     * @param string $sha
     * @return $this
     */
    function setSha($sha);

    function generateSha();

    /**
     * @return string
     */
    function getSha();

    /**
     * @return string
     */
    function getOwner();

    /**
     * @param string $owner
     * @return $this
     */
    function setOwner($owner);

    /**
     * @return string
     */
    function getToken();

    /**
     * @param string $token
     * @return $this
     */
    function setToken($token);

    /**
     * @return array
     */
    function getTags();

    /**
     * @param string $tags
     * @return $this
     */
    function setTags($tags);

    /**
     * @param string $removeTags
     * @return $this
     */
    function setRemoveTags($removeTags);

    /**
     * @return array
     */
    function getRemoveTags();

    /**
     * @param array $ignoredDomains
     * @return $this
     */
    function setIgnoredDomains($ignoredDomains);

    /**
     * @return array
     */
    function getIgnoredDomains();

    /**
     * @param string $domain
     * @return $this
     */
    function setDomain($domain);

    /**
     * @return string
     */
    function getDomain();

    /**
     * @param mixed $param //ApiDoubleOptIn object is preferred
     * @return $this
     */
    function setApiDoubleOptIn($param);

    /**
     * @return ApiDoubleOptIn
     */
    function getApiDoubleOptIn();

    /**
     * @param bool $param
     * @return $this
     */
    function setActiveSynchronization($param);

    /**
     * @return bool
     */
    function getActiveSynchronization();

    /**
     * @param bool $param
     * @return $this;
     */
    function setRequireSynchronization($param);

    /**
     * @return bool
     */
    function getRequireSynchronization();

    /**
     * @param int $param
     * @return $this;
     */
    function setCookieTtl($param);

    /**
     * @return int
     */
    function getCookieTtl();

    /**
     * @param string $param
     * @return $this
     */
    function setConfSchemaVer($param);

    /**
     * @return string
     */
    function getConfSchemaVer();

    /**
     * @param $param
     * @return $this
     */
    function setOwnersList($param);

    /**
     * @return array
     */
    function getOwnersList();

    /**
     * @param $param
     * @return $this
     */
    function setLocation($param);

    /**
     * @return string
     */
    function getLocation();

    /**
     * Set reporting service to active state
     *
     * @return $this
     */
    function setActiveReporting($active);

    /**
     * Return active state for reporting
     *
     * @return bool
     */
    function getActiveReporting();

    /**
     * @return mixed
     */
    function setRequestClientConf(RequestClientConfigurationInterface $RequestClientConf);

    /**
     * @return RequestClientConfigurationInterface
     */
    function getRequestClientConf();
}
