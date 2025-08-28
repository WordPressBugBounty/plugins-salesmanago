<?php


namespace SALESmanago\Entity;

/**
 * Interface ReportConfigurationInterface
 * @package SALESmanago\Entity
 * @deprecated since 3.0.11
 */
interface ReportConfigurationInterface
{
    /**
     * @param $param
     * @return mixed
     * @deprecated since 3.0.11
     */
    public function setActiveDebugger($param);

    /**
     * @return bool
     * @deprecated since 3.0.11
     */
    public function isActiveDebugger();

    /**
     * @param $param
     * @return mixed
     * @deprecated since 3.0.11
     */
    public function setActiveHealth($param);

    /**
     * @return bool
     * @deprecated since 3.0.11
     */
    public function isActiveHealth();

    /**
     * @param $param
     * @return mixed
     * @deprecated since 3.0.11
     */
    public function setActiveUsage($param);

    /**
     * @return bool
     * @deprecated since 3.0.11
     */
    public function isActiveUsage();

    /**
     * @param string $param
     * @return $this
     * @deprecated since 3.0.11
     */
    public function setDebuggerUrl($param);

    /**
     * @return string
     * @deprecated since 3.0.11
     */
    public function getDebuggerUrl();

    /**
     * @param string $param
     * @return $this
     * @deprecated since 3.0.11
     */
    public function setHealthUrl($param);

    /**
     * @return string
     */
    public function getHealthUrl();

    /**
     * @param string $param
     * @return $this
     * @deprecated since 3.0.11
     */
    public function setUsageUrl($param);

    /**
     * @deprecated since 3.0.11
     * @return string
     */
    public function getUsageUrl();
}