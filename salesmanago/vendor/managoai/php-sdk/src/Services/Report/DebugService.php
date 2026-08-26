<?php


namespace SALESmanago\Services\Report;

use SALESmanago\Model\Report\DebugModel;

/**
 * Request data log on chosen stages;
 *
 * Class DebugService
 * @package SALESmanago\Services\Health
 * @deprecated since 3.0.11
 */
class DebugService extends AbstractReportService
{
    protected function setModel()
    {
        return new DebugModel();
    }

    protected function setUpRequest()
    {
        $this->RequestHelper->setUrl($this->conf->getDebuggerUrl());
        return $this;
    }

    public function setData($data)
    {
        $this->model->setData($data);
        return $this;
    }
}