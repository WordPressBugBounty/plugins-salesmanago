<?php


namespace SALESmanago\Model\Report;

/**
 * Class UsageModel
 * @package SALESmanago\Model\Report
 * @deprecated since 3.0.11
 */
class UsageModel extends AbstractBasicReportModel
{
    const TYPE_USAGE = 'usage';

    /**
     * @return array|mixed
     */
    public function getDataToSend()
    {
        return [
            self::TYPE => self::TYPE_USAGE,
            self::BASIC_DATA => $this->getBasicReportData(),
            self::DATA => $this->rawData
        ];
    }
}