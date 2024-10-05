<?php


namespace SALESmanago\Services\Report;


use SALESmanago\Entity\ReportConfigurationInterface;
use SALESmanago\Helper\SimpleRequestHelper;
use SALESmanago\Model\Report\ReportModelInterface;

/**
 * Class AbstractReportService
 * @package SALESmanago\Services\Report
 * @deprecated since 3.0.11
 */
abstract class AbstractReportService
{
    /**
     * @var SimpleRequestHelper
     */
    protected $RequestHelper;

    /**
     * @var - HealthModelInterface one of Health model
     */
    protected $model;

    /**
     * @var ReportConfigurationInterface
     */
    protected $conf;

    public function __construct(ReportConfigurationInterface $conf)
    {
        $this->RequestHelper = new SimpleRequestHelper();
        $this->model = $this->setModel();
        $this->conf = $conf;
    }

    /**
     * @return ReportModelInterface
     */
    protected abstract function setModel();

    /**
     * Set data to transfer;
     * @param mixed $data
     * @return $this;
     */
    public abstract function setData($data);

    /**
     * Basically using to set endpoint for reporting;
     * @return mixed
     */
    protected abstract function setUpRequest();

    /**
     * Do reporting
     */
    public function doReport()
    {
        $this->RequestHelper->curlCall($this->model->getDataToSend());
    }
}