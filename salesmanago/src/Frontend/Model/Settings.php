<?php

namespace bhr\Frontend\Model;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\Configuration;

class Settings extends AbstractModel
{
    protected $Configuration;
    protected $PlatformSettings;

    protected $isUserAuthorized = false;
    protected $activePlugins = array();

    public function __construct()
    {
        //Get global db
        parent::__construct();

        foreach (SUPPORTED_PLUGINS as $key => $value)
        {
            $this->activePlugins[$value] = false;
        }

        try {
            //Get Configuration frm DB and set to library
            $configurationJson = $this->getConfigurationFromDB();
            if(!$configurationJson) {
                return false;
            }

            //Set Configuration entity
            $this->Configuration = Configuration::getInstance();
            $config = json_decode($configurationJson);

            $this->Configuration
                ->setClientId($config->clientId)
                ->setEndpoint($config->endpoint)
                ->setApiKey($config->apiKey)
                ->setToken($config->token)
                ->setOwner($config->owner)
                ->setSha($config->sha)
                ->setContactCookieTtl(!empty($config->contactCookieTtl)
                    ? $config->contactCookieTtl
                    : Configuration::DEFAULT_CONTACT_COOKIE_TTL)
                ->setEventCookieTtl(isset($config->eventCookieTtl)
                    ? $config->eventCookieTtl
                    : $config->cookieTtl)
                ->setIgnoredDomains($config->ignoredDomains)
                ->setLocation($config->location)
                ->setSmApp($config->smApp);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        try {
            //Do not continue if user is not logged in
            $this->setIsUserAuthorized();
            if(!$this->isUserAuthorized) {
                return false;
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        try {
            //Get Platform Settings as set Settings Model
            $platformSettingsJson = $this->getPlatformSettingsFromDb();
            if(!$platformSettingsJson) {
                return false;
            }
            $this->PlatformSettings = json_decode($platformSettingsJson);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this;
    }

    /**
     * @return false
     */
    private function getConfigurationFromDB()
    {
        $configurationJson = $this->db->get_row($this->db->prepare(
            "SELECT option_value FROM {$this->db->options} WHERE option_name = %s LIMIT 1",
            self::CONFIGURATION
        ), ARRAY_A);
        if ($configurationJson == null || empty($configurationJson) || $configurationJson == '{}') {
            return false;
        }
        return $configurationJson['option_value'];
    }

    /**
     * @return false
     */
    private function getPlatformSettingsFromDb()
    {
        $platformSettingsJson = $this->db->get_row($this->db->prepare(
            "SELECT option_value FROM {$this->db->options} WHERE option_name = %s LIMIT 1",
            self::PLATFORM_SETTINGS
        ), ARRAY_A);
        if ($platformSettingsJson == null || empty($platformSettingsJson) || $platformSettingsJson == '{}') {
            return false;
        }
        return $platformSettingsJson['option_value'];
    }

    /**
     * @return mixed|Configuration
     */
    public function getConfiguration()
    {
        return $this->Configuration;
    }

    /**
     * @param mixed|Configuration $Configuration
     */
    public function setConfiguration($Configuration)
    {
        $this->Configuration = $Configuration;
    }

    /**
     * @return mixed|null
     */
    public function getPlatformSettings()
    {
        return $this->PlatformSettings;
    }

    /**
     * @param mixed|null $PlatformSettings
     */
    public function setPlatformSettings($PlatformSettings)
    {
        $this->PlatformSettings = $PlatformSettings;
    }


    /**
     *
     */
    private function setIsUserAuthorized()
    {
        if(isset($this->Configuration)) {
            if(!empty($this->Configuration->getClientId())
            && !empty($this->Configuration->getEndpoint())
            && !empty($this->Configuration->getSha())
            && !empty($this->Configuration->getToken())
            ) {
                $this->isUserAuthorized = true;
            }
        }
    }

    /**
     * @return bool
     */
    public function isUserAuthorized()
    {
        return $this->isUserAuthorized;
    }

    /**
     * @return bool[]
     */
    public function getActivePlugins()
    {
        return $this->activePlugins;
    }


    /**
     * @return string|null
     */
    public function getClientId()
    {
        return empty($this->json->Connection->clientId)
            ? null
            : $this->Configuration->getClientId();
    }

    /**
     * @return string|null
     */
    public function getEndpoint()
    {
        return empty($this->json->Connection->endpoint)
            ? 'app2.salesmanago.com'
            : $this->Configuration->getEndpoint();
    }

    /**
     * @param $name
     * @return bool
     */
    public function isPluginActive($name)
    {
        if(isset($this->activePlugins[$name])) {
            return boolval($this->activePlugins[$name]);
        }
        return false;
    }
}
