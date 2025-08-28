<?php


namespace SALESmanago\Entity;

use JsonSerializable;
use SALESmanago\Exception\Exception;

class Configuration extends AbstractConfiguration implements ConfigurationInterface, ReportConfigurationInterface, JsonSerializable
{
    const
        ACTIVE                   = 'active',
        ENDPOINT                 = 'endpoint',
        CLIENT_ID                = 'clientId',
        API_KEY                  = 'apiKey',
        API_SECRET               = 'apiSecret',
        OWNER                    = 'owner',
        EMAIL                    = 'email',
        SHA                      = 'sha',
        TOKEN                    = 'token',
        IGNORED_DOMAINS          = 'ignoredDomains',
        CONTACT_COOKIE_TTL       = 'contactCookieTtl',
        EVENT_COOKIE_TTL         = 'eventCookieTtl',
        RETRY_REQUEST_IF_TIMEOUT = 'retryRequestIfTimeout',
        SM_APP                   = 'smapp';

    /**
     * Flag to detect if module/component/plugin is active on platform;
     * @var boolean
     */
    protected $active = false;

    /**
     * @var string|null
     */
    protected $endpoint = 'https://app2.salesmanago.pl';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiSecret;

    /**
     * @var string
     */
    protected $owner;

    /**
     * @var string
     */
    protected $sha;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $tags;

    /**
     * @var array
     */
    protected $removeTags;

    /**
     * @var array
     */
    protected $ignoredDomains;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $domainToken;

    /**
     * @var array
     */
    protected $consentDetails;

    /**
     * @var ApiDoubleOptIn
     */
    protected $ApiDoubleOptIn;

    /**
     * @var bool active state for Synchronization Service;
     */
    protected $activeSynchronization = false;

    /**
     * @var bool Shows if synchronization needed for particular entity (Contact)
     */
    protected $requireSynchronization = false;

    /**
     * @var int Time in seconds for Contact Cookie expire Time
     */
    protected $contactCookieTtl = self::DEFAULT_CONTACT_COOKIE_TTL; //10 yrs

    /**
     * @var int Time in seconds for Event Cookie expire Time
     */
    protected $eventCookieTtl = self::DEFAULT_EVENT_COOKIE_TTL; //12 h

    /**
     * @var array|null - owners list for SM account
     */
    protected $ownersList = null;

    /**
     * @var string unique store id for SALESmanago (used with events, feed)
     */
    protected $location;

    /**
     * Configuration schema version used to specify
     * which settings version is used by client;
     * 1.0.0: initial version
     * 1.1.0: cookieTtl changed to eventCookieTtl, added contactCookieTtl
     * @var string - version
     */
    protected $confSchemaVer = '1.1.0';

    /**
     * @var string url with ssl
     */
    protected $usageUrl;

    /**
     * @var string url with ssl
     */
    protected $healthUrl;

    /**
     * @var string url with ssl
     */
    protected $debugUrl;

    /**
     * @var bool
     */
    protected $activeUsage = false;

    /**
     * @var bool
     */
    protected $activeDebugger = false;

    /**
     * @var bool
     */
    protected $activeHealth = false;

    /**
     * @var bool
     */
    protected $activeReporting = false;

    /**
     * @var string platform name
     */
    private $platformName = 'unavailable';

    /**
     * @var string exact platform version
     */
    private $platformVersion = 'unavailable';

    /**
     * @var string exact version od integration (plugin version or app version)
     */
    private $versionOfIntegration = 'unavailable';

    /**
     * @var string url of platform where plugin or integration is installed
     */
    private $platformDomain = 'unavailable';

    /**
     * @var string platform admin panel lang;
     */
    private $platformLang = 'unavailable';

    /**
     * @var string
     */
    private $platformCountry = 'unavailable';

    /**
     * @var RequestClientConfigurationInterface;
     */
    private $RequestClientConf;

    /**
     * Retry request in case of 428 error code in curl client response
     *
     * @var bool flag
     */
    private $retryRequestIfTimeout = false;

    /**
     * Salesmanago application id
     * @var null|int
     */
    protected $smApp = null;

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Set salesmanago app id
     *
     * @param int|null $param
     * @return $this
     */
    public function setSmApp(?int $param)
    {
        $this->smApp = $param;
        return $this;
    }

    /**
     * Return salesmanago app id
     *
     * @return int|null
     */
    public function getSmApp()
    {
        return $this->smApp;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     * @param boolean $ssl
     * @return $this
     */
    public function setEndpoint($endpoint, $ssl = true)
    {
        if (empty($endpoint)) {//endpoint stay default
            return $this;
        }

        if (preg_match('/^https?:\/\//', $endpoint)) {
            if ($ssl === true && preg_match("@^(?:http:\/\/)([^/]+)@i", $endpoint, $matches)) {
                $this->endpoint = "https://" . $matches[1];
            } else {
                $this->endpoint = $endpoint;
            }
        } else {
            if ($ssl === true) {
                $this->endpoint = "https://" . $endpoint;
            } else {
                $this->endpoint = "http://" . $endpoint;
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        if (empty($this->apiKey)) {
            $this->apiKey = md5(time() . mt_rand());
        }

        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return $this
     */
    public function setDefaultApiKey()
    {
        $this->apiKey = md5(time() . mt_rand());
        return $this;
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * @param string $apiSecret
     * @return $this
     */
    public function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
        return $this;
    }

    /**
     * @param string $sha
     * @return $this
     */
    public function setSha($sha)
    {
        $this->sha = $sha;
        return $this;
    }

    public function generateSha()
    {
        $this->sha = sha1($this->getApiKey() . $this->getClientId() . $this->getApiSecret());
        return $this;
    }

    /**
     * @return string
     */
    public function getSha()
    {
        if (empty($this->sha)) {
            self::generateSha();
        }
        return $this->sha;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $tags = explode(',', $tags);
        foreach ($tags as $key => $tag) {
            $tags[$key] = strtoupper(trim($tag));
        }
        $this->tags = $tags;
        return $this;
    }

    /**
     * @param string $removeTags
     * @return $this
     */
    public function setRemoveTags($removeTags)
    {
        $removeTags = explode(',', $removeTags);
        foreach ($removeTags as $key => $tag) {
            $removeTags[$key] = strtoupper(trim($tag));
        }
        $this->removeTags = $removeTags;
        return $this;
    }

    /**
     * @return array
     */
    public function getRemoveTags()
    {
        return $this->removeTags;
    }

    /**
     * @param array $ignoredDomains
     * @return $this
     */
    public function setIgnoredDomains($ignoredDomains)
    {
        if (is_array($ignoredDomains)) {
            $this->ignoredDomains = $ignoredDomains;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getIgnoredDomains()
    {
        return $this->ignoredDomains;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domainToken
     * @return $this
     */
    public function setDomainToken($domainToken)
    {
        $this->domainToken = $domainToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomainToken()
    {
        return $this->domainToken;
    }

    /**
     * @param  array $params
     * @return int
     */
    public function count($params)
    {
        return !is_null($params) && is_array($params) ? count($params) : 0;
    }

    public function getUserIP() {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ip = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = 'UNKNOWN';

        $ips = explode(",",$ip);
        if(is_array($ips)) {
            return trim($ips[0]);
        }

        return $ip;
    }

    /**
     * @param mixed $param  //ApiDoubleOptIn object is preferred
     * @return $this
     */
    public function setApiDoubleOptIn($param)
    {
        if (is_array($param)) {
            $this->ApiDoubleOptIn = new ApiDoubleOptIn($param);
        } else if ($param instanceof ApiDoubleOptIn) {
            $this->ApiDoubleOptIn = $param;
        }

        return $this;
    }

    /**
     * @return ApiDoubleOptIn
     */
    public function getApiDoubleOptIn()
    {
        return isset($this->ApiDoubleOptIn) ? $this->ApiDoubleOptIn : new ApiDoubleOptIn();
    }

    /**
     * @param bool $param
     * @return $this
     */
    public function setActiveSynchronization($param)
    {
        $this->activeSynchronization = $param;
        return $this;
    }

    /**
     * @return bool
     */
    public function getActiveSynchronization()
    {
        return $this->activeSynchronization;
    }

    /**
     * @param bool $param
     * @return $this;
     */
    public function setRequireSynchronization($param)
    {
        $this->requireSynchronization = $param;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRequireSynchronization()
    {
        return $this->requireSynchronization;
    }

    /**
     * @return int
     */
    public function getContactCookieTtl()
    {
        return $this->contactCookieTtl;
    }

    /**
     * @param int $contactCookieTtl
     * @return $this
     */
    public function setContactCookieTtl($contactCookieTtl)
    {
        $this->contactCookieTtl = intval($contactCookieTtl);
        return $this;
    }

    /**
     * @return int
     */
    public function getEventCookieTtl()
    {
        return $this->eventCookieTtl;
    }

    /**
     * @param int $eventCookieTtl
     * @return $this
     */
    public function setEventCookieTtl($eventCookieTtl)
    {
        $this->eventCookieTtl = intval($eventCookieTtl);
        return $this;
    }

    /**
     * @deprecated
     * @param int $param
     * @return $this;
     * Use setEventCookieTtl instead
     */
    public function setCookieTtl($param)
    {
        if(is_int($param) && $param >= 0) {
            $this->eventCookieTtl = $param;
        }
        return $this;
    }

    /**
     * @deprecated
     * @return int
     * Use getEventCookieTtl instead
     */
    public function getCookieTtl()
    {
        return $this->eventCookieTtl;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setConfSchemaVer($param)
    {
        $this->confSchemaVer = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfSchemaVer()
    {
        return $this->confSchemaVer;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setOwnersList($param)
    {
        $this->ownersList = $param;
        return $this;
    }

    /**
     * @return array
     */
    public function getOwnersList()
    {
        return $this->ownersList;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setLocation($param)
    {
        $this->location = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function setActiveReporting($active)
    {
        $this->activeReporting = $active;
        return $this;
    }

    public function getActiveReporting()
    {
        return $this->activeReporting;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setPlatformName($param)
    {
        $this->platformName = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformName()
    {
        return $this->platformName;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setPlatformVersion($param)
    {
        $this->platformVersion = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformVersion()
    {
        return $this->platformVersion;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setVersionOfIntegration($param)
    {
        $this->versionOfIntegration = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersionOfIntegration()
    {
        return $this->versionOfIntegration;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setPlatformDomain($param)
    {
        $this->platformDomain = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformDomain()
    {
        return $this->platformDomain;
    }

    /**
     * @return string
     */
    public function getPhpVersion()
    {
        return phpversion();
    }

    /**
     * Set platform admin panel lang
     *
     * @param string $param
     */
    public function setPlatformLang($param)
    {
        $this->platformLang = $param;
        return $this;
    }

    /**
     * Return platform admin panel lang
     */
    public function getPlatformLang()
    {
        return $this->platformLang;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setPlatformCountry($param)
    {
        $this->platformCountry = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformCountry()
    {
        return $this->platformCountry;
    }

    /**
     * @deprecated since 3.0.11
     * @param string $param
     * @return $this
     */
    public function setDebuggerUrl($param)
    {
        $this->debugUrl = $param;
        return $this;
    }

    /**
     * @deprecated since 3.0.11
     * @deprecated
     * @return string
     */
    public function getDebuggerUrl()
    {
        return $this->debugUrl;
    }

    /**
     * @deprecated since 3.0.11
     * @param string $param
     * @return $this
     */
    public function setHealthUrl($param)
    {
        $this->healthUrl = $param;
        return $this;
    }

    /**
     * @deprecated since 3.0.11
     * @return string
     */
    public function getHealthUrl()
    {
        return $this->healthUrl;
    }

    /**
     * @deprecated since 3.0.11
     * @param string $param
     * @return $this
     */
    public function setUsageUrl($param)
    {
        $this->usageUrl= $param;
        return $this;
    }

    /**
     * @deprecated since 3.0.11
     * @return string
     */
    public function getUsageUrl()
    {
        return $this->usageUrl;
    }

    /**
     * @deprecated since 3.0.11
     * @param $param
     * @return $this|mixed
     */
    public function setActiveDebugger($param)
    {
        $this->activeDebugger = $param;
        return $this;
    }

    /**
     * @deprecated since 3.0.11
     * @param $param
     * @return $this|mixed
     */
    public function setActiveHealth($param)
    {
        $this->activeHealth = $param;
        return $this;
    }

    /**
     * @deprecated since 3.0.11
     * @param $param
     * @return $this|mixed
     */
    public function setActiveUsage($param)
    {
        $this->activeUsage = $param;
        return $this;
    }

    /**
     * @deprecated since 3.0.11
     * @return bool
     */
    public function isActiveDebugger()
    {
        return $this->activeDebugger;
    }

    /**
     * @deprecated since 3.0.11
     * @return bool
     */
    public function isActiveHealth()
    {
        return $this->activeHealth;
    }

    /**
     * @deprecated since 3.0.11
     * @return bool
     */
    public function isActiveUsage()
    {
        return $this->activeUsage;
    }

    /**
     * @param RequestClientConfigurationInterface $RequestClientConf
     * @return self
     */
    public function setRequestClientConf(RequestClientConfigurationInterface $RequestClientConf)
    {
        $this->RequestClientConf = $RequestClientConf;

        if (empty($RequestClientConf->getUrl())
            && empty($RequestClientConf->getHost())
        ) {
            $this->RequestClientConf->setUrl($this->getEndpoint());
        }

        return $this;
    }

    /**
     * @param array|null $data
     * @return RequestClientConfigurationInterface|null
     * @throws Exception
     */
    public function getRequestClientConf($data = null)
    {
        if ($data != null) {
            if (isset($this->RequestClientConf)) {
                $this->RequestClientConf->setDataWithSetters($data);
                return $this->RequestClientConf;
            }
            return new cUrlClientConfiguration($data);
        } elseif (isset($this->RequestClientConf)) {
            return $this->RequestClientConf;
        }

        return new cUrlClientConfiguration();
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setRetryRequestIfTimeout($bool)
    {
        $this->retryRequestIfTimeout = $bool;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRetryRequestIfTimeout()
    {
        return $this->retryRequestIfTimeout;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (array)$this;
    }
}
