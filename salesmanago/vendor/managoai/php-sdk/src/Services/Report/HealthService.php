<?php


namespace SALESmanago\Services\Report;

use SALESmanago\Model\Report\HealthModel;

/**
 * Exception log to external SM server
 *
 * Class HealthService
 * @package SALESmanago\Services\Health
 * @deprecated since 3.0.11
 */
class HealthService extends AbstractReportService
{
    protected function setModel()
    {
        return new HealthModel();
    }

    protected function setUpRequest()
    {
        $this->RequestHelper->setUrl($this->conf->getHealthUrl());
        return $this;
    }

    public function setData($data)
    {
        $this->model->setData($data);
        return $this;
    }
}