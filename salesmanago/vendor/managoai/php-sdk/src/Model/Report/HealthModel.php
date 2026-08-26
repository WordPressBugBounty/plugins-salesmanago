<?php


namespace SALESmanago\Model\Report;

/**
 * Class HealthModel
 * @package SALESmanago\Model\Report
 * @deprecated since 3.0.11
 */
class HealthModel extends AbstractBasicReportModel
{
    const TYPE_HEALTH = 'health';

    /**
     * @return array|mixed
     */
    public function getDataToSend()
    {
        return [
            self::TYPE => self::TYPE_HEALTH,
            self::BASIC_DATA => $this->getBasicReportData(),
            self::DATA => $this->rawData
        ];
    }
}