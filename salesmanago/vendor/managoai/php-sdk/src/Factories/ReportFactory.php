<?php


namespace SALESmanago\Factories;


use Exception;
use SALESmanago\Entity\ReportConfigurationInterface;

use SALESmanago\Services\Report\DebugService;
use SALESmanago\Services\Report\HealthService;
use SALESmanago\Services\Report\UsageService;

class ReportFactory
{
    /**
     * @param ReportConfigurationInterface $conf
     * @param $data
     * @return bool
     */
    public static function doHealthReport(ReportConfigurationInterface $conf, $data)
    {
        try {
            if (!$conf->isActiveHealth()) {
                return true;
            }

            $healthReportService = new HealthService($conf);
            $healthReportService
                ->setData($data)
                ->doReport();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param ReportConfigurationInterface $conf
     * @param $data
     * @return bool
     */
    public static function doUsageReport(ReportConfigurationInterface $conf, $data)
    {
        try {
            if (!$conf->isActiveUsage()) {
                return true;
            }

            $healthReportService = new UsageService($conf);
            $healthReportService
                ->setData($data)
                ->doReport();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param ReportConfigurationInterface $conf
     * @param $data
     * @return bool
     */
    public static function doDebugReport(ReportConfigurationInterface $conf, $data)
    {
        try {
            if (!$conf->isActiveDebugger()) {
                return true;
            }

            $healthReportService = new DebugService($conf);
            $healthReportService
                ->setData($data)
                ->doReport();
            return true;
        } catch (Exception $exception) {
            return false;
        }

    }
}