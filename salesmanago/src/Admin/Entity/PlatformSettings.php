<?php

namespace bhr\Admin\Entity;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\Plugins\MonitCode;
use bhr\Admin\Entity\Plugins\Wp  as PluginWp;
use bhr\Admin\Entity\Plugins\Wc  as PluginWc;
use bhr\Admin\Entity\Plugins\Cf7 as PluginCf7;
use bhr\Admin\Entity\Plugins\Gf  as PluginGf;
use bhr\Admin\Entity\Plugins\Ff  as PluginFf;
use SALESmanago\Exception\Exception;

final class PlatformSettings implements \JsonSerializable
{
    private static $instances = [];

    protected $PluginWp;
    protected $PluginWc;
    protected $PluginCf7;
    protected $PluginGf;
    protected $PluginFf;
    protected $MonitCode;

    protected $updatedAt;
    protected $languageDetection     = 'platform';
    protected $pluginVersion         = null;

    private function __construct()
    {
        $this->PluginWp  = new PluginWp();
        $this->PluginWc  = new PluginWc();
        $this->PluginCf7 = new PluginCf7();
        $this->PluginGf  = new PluginGf();
        $this->PluginFf  = new PluginFf();
        $this->MonitCode = new MonitCode();
    }

    protected function __clone()
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    /**
     * @return mixed|static
     */
    public static function getInstance()
    {
        $cls = PlatformSettings::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new self();
        }

        return self::$instances[$cls];
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return PluginWp
     */
    public function getPluginWp()
    {
        return $this->PluginWp;
    }


    /**
     * @param $PluginWp
     * @return $this
     */
    public function setPluginWp($PluginWp)
    {
        $this->PluginWp = $PluginWp;
        return $this;
    }

    /**
     * @return PluginWc
     */
    public function getPluginWc()
    {
        return $this->PluginWc;
    }


    /**
     * @param $PluginWc
     * @return $this
     */
    public function setPluginWc($PluginWc)
    {
        $this->PluginWc = $PluginWc;
        return $this;
    }

    /**
     * @return PluginCf7
     */
    public function getPluginCf7()
    {
        return $this->PluginCf7;
    }


    /**
     * @param $PluginCf7
     * @return $this
     */
    public function setPluginCf7($PluginCf7)
    {
        $this->PluginCf7 = $PluginCf7;
        return $this;
    }

    /**
     * @return PluginGf
     */
    public function getPluginGf()
    {
        return $this->PluginGf;
    }


    /**
     * @param $PluginGf
     * @return $this
     */
    public function setPluginGf($PluginGf)
    {
        $this->PluginGf = $PluginGf;
        return $this;
    }

    /**
     * @return PluginFf
     */
    public function getPluginFf()
    {
        return $this->PluginFf;
    }

    /**
     * @param $PluginFf
     * @return $this
     */
    public function setPluginFf($PluginFf)
    {
        $this->PluginFf = $PluginFf;
        return $this;
    }

    /**
     * @param $name
     * @return PluginCf7|PluginGf|PluginWc|PluginWp|PluginFf|null
     */
    public function getPluginByName($name)
    {
        if ($name == SUPPORTED_PLUGINS['WordPress']) {
            return $this->PluginWp;
        } elseif ($name == SUPPORTED_PLUGINS['WooCommerce']) {
            return $this->PluginWc;
        } elseif ($name == SUPPORTED_PLUGINS['Contact Form 7']) {
            return $this->PluginCf7;
        } elseif ($name == SUPPORTED_PLUGINS['Gravity Forms']) {
            return $this->PluginGf;
        } elseif ($name == SUPPORTED_PLUGINS['Fluent Forms']){
            return $this->PluginFf;
        }
        return null;
    }

    /**
     * @return MonitCode
     */
    public function getMonitCode()
    {
        return $this->MonitCode;
    }

    /**
     * @param MonitCode $MonitCode
     */
    public function setMonitCode($MonitCode)
    {
        $this->MonitCode = $MonitCode;
    }

    /**
     * @param $name
     * @return bool|null
     */
    public function isActive($name)
    {
        if ($Plugin = $this->getPluginByName($name)) {
            return $Plugin->isActive();
        }
        return null;
    }


    /**
     * @return array|mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $out = array();
        foreach (get_object_vars($this) as $key => $val) {
            if (is_object($val)) {
                $out[$key] = $val->toArray();
            } else {
                $out[$key] = $val;
            }
        }
        return $out;
    }

    /**
     * @return array
     */
    private function toArray()
    {
        $out = array();
        foreach (get_object_vars($this) as $key => $val) {
            if (is_object($val) && !is_array($val)) {
                $out[$key] = $val->toArray();
            } else {
                $out[$key] = $val;
            }
        }
        return $out;
    }

    /**
     * @return string
     */
    public function getLanguageDetection()
    {
        return $this->languageDetection;
    }

    /**
     * @param string $languageDetection
     * @return PlatformSettings
     */
    public function setLanguageDetection($languageDetection = 'platform')
    {
        if (!empty($languageDetection) && ($languageDetection === 'platform' || $languageDetection === 'browser')) {
            $this->languageDetection = $languageDetection;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->pluginVersion;
    }

    /**
     * @param string $pluginVersion
     */
    public function setPluginVersion($pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;
        return $this;
    }
}
