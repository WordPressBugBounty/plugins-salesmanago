<?php


namespace SALESmanago\Model\Report;

/**
 * Interface ReportModelInterface
 * @package SALESmanago\Model\Report
 * @deprecated since 3.0.11
 */
interface ReportModelInterface
{
    public function getDataToSend();

    public function setData($data);
}