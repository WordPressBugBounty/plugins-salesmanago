<?php


namespace SALESmanago\Model\Report;

/**
 * Class DebugModel
 * @package SALESmanago\Model\Report
 * @deprecated since 3.0.11
 */
class DebugModel extends AbstractBasicReportModel
{
    const TYPE_DEBUG = 'debug';

    /**
     * @return array
     */
    public function getDataToSend()
    {
        return [
            self::TYPE => self::TYPE_DEBUG,
            self::BASIC_DATA => $this->getBasicReportData(),
            self::DATA => $this->rawData
        ];
    }
}