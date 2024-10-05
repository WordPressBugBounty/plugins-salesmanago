<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

class MonitCode extends AbstractPlugin
{
    protected $disableMonitoringCode;
    protected $smCustom;
    protected $smBanners;
    protected $active = true;
    protected $popUpJs;

    /**
     * @param $pluginSettings
     * @return $this|MonitCode
     */
    public function setPluginSettings($pluginSettings)
    {
        parent::setPluginSettings($pluginSettings);

        $this->setDisableMonitoringCode(
            isset($pluginSettings->disableMonitoringCode)
                ? $pluginSettings->disableMonitoringCode
                : false
        );
        $this->setSmCustom(
            isset($pluginSettings->smCustom)
                ? $pluginSettings->smCustom
                : false
        );
        $this->setSmBanners(
            isset($pluginSettings->smBanners)
                ? $pluginSettings->smBanners
                : false
        );
        $this->setPopUpJs(
            isset($pluginSettings->popUpJs)
                ? $pluginSettings->popUpJs
                : false
        );
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableMonitoringCode()
    {
        return $this->disableMonitoringCode;
    }

    /**
     * @param $disableMonitoringCode
     *
     * @return MonitCode
     */
    public function setDisableMonitoringCode($disableMonitoringCode)
    {
        $this->disableMonitoringCode = $disableMonitoringCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPopUpJs()
    {
        return $this->popUpJs;
    }

    /**
     * @param  bool  $popUpJs
     * @return MonitCode
     */
    public function setPopUpJs($popUpJs)
    {
        $this->popUpJs = $popUpJs;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSmCustom()
    {
        return $this->smCustom;
    }

    /**
     * @param bool $smCustom
     * @return MonitCode
     */
    public function setSmCustom($smCustom)
    {
        $this->smCustom = $smCustom;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSmBanners()
    {
        return $this->smBanners;
    }

    /**
     * @param bool $smBanners
     * @return MonitCode
     */
    public function setSmBanners($smBanners)
    {
        $this->smBanners = $smBanners;
        return $this;
    }
}
