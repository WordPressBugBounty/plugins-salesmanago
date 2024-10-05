<?php


namespace SALESmanago\Services\Report;


use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Exception\Exception as SalesmanagoException;
use SALESmanago\Model\Report\ReportModel;
use SALESmanago\Entity\Reporting\Platform;
use SALESmanago\Services\ContactAndEventTransferService;
use Exception;
use Error;

/**
 * Class ReportService
 * Please instantiating this as soon as get proper ReportConfigurationInterface configuration
 *
 * @package SALESmanago\Services\Report
 */
class ReportService
{
    /**
     * @var ConfigurationInterface
     */
    private $conf;

    /**
     * @var ReportModel
     */
    private $reportModel;

    /**
     * @var ContactAndEventTransferService
     */
    private $transferService;

    /**
     * @var array of instances
     */
    private static $instances = [];

    /**
     * @var string reporting proxy endpoint
     */
    protected $endpoint = 'https://survey.salesmanago.com/2.0';

    /**
     * @var string
     */
    private $customerEndpoint;

    /**
     * ReportService constructor.
     *
     * @param ConfigurationInterface $conf
     */
    final private function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
        $this->customerEndpoint = $this->conf->getEndpoint();
        $this->reportModel = new ReportModel($this->conf);
    }

    protected function __clone() {}

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    /**
     * @param ConfigurationInterface|null $conf
     * @return mixed|static
     * @throws Exception
     */
    public static function getInstance($conf = null)
    {
        $cls = static::class;

        if (!isset(self::$instances[$cls])) {

            if ($conf === null) {
                return null;
            }

            self::$instances[$cls] = new static($conf);
        }

        return self::$instances[$cls];
    }

    /**
     * @param $actType - one of ReportModel const ACT_...
     * @param array $additionalInformation
     *
     * @return bool
     */
    public function reportAction($actType, array $additionalInformation = []): bool
    {
        if (!$this->conf->getActiveReporting()) {
            return false;
        }

        try {
            $this->endpointsExchange();

            $this->transferService = new ContactAndEventTransferService($this->conf);
            $this->transferService->transferBoth(
                $this->reportModel->getClientAsContact($actType),
                $this->reportModel->getActionAsEvent($actType, $additionalInformation)
            );

            $this->endpointsExchange();
            return true;
        } catch (SalesmanagoException|Exception|Error $e) {
            $this->endpointsExchange();
            return false;
        }
    }

    /**
     * Report of export action
     *
     * @param string $exportType - see ReportModel const EXPORT_TYPE_...
     * @param int $exportedPackages
     * @param int $exportedItems
     * @param string $storeIdentifier - Identifier which help to know from which store was export, readable value
     * @return bool
     */
    public function reportActionExport(
        string $exportType,
        int $exportedPackages,
        int $exportedItems,
        string $storeIdentifier
    ): bool {
        try {
            $this->endpointsExchange();

            $this->transferService = new ContactAndEventTransferService($this->conf);
            $this->transferService->transferBoth(
                $this->reportModel->getClientAsContact(ReportModel::ACT_EXPORT),
                $this->reportModel->getActionAsEvent(
                    ReportModel::ACT_EXPORT,
                    [
                        ReportModel::EXPORT_TYPE => $exportType,
                        ReportModel::PACKAGES    => $exportedPackages,
                        ReportModel::ITEMS       => $exportedItems,
                        ReportModel::STORE       => $storeIdentifier
                    ]
                )
            );

            $this->endpointsExchange();
            return true;
        } catch (SalesmanagoException|Exception|Error $e) {
            $this->endpointsExchange();
            return false;
        }
    }

    /**
     * Report exceptions from SALESmanago\Exception\Exception
     *
     * @param $exceptionViewMessage
     * @return bool
     */
    public function reportException($exceptionViewMessage): bool
    {
        return $this->reportAction(ReportModel::ACT_EXCEPTION, [$exceptionViewMessage]);
    }

    /**
     * Change configuration endpoint in configuration object for report
     *
     * @return void
     */
    protected function endpointsExchange(): void
    {
        if ($this->conf->getEndpoint() == $this->endpoint) {
            $this->conf->setEndpoint($this->customerEndpoint);
            $this->conf->setRequestClientConf(
                $this->conf->getRequestClientConf()->setHost($this->customerEndpoint)
            );
        } else {
            $this->conf->setEndpoint($this->endpoint);
            $this->conf->setRequestClientConf(
                $this->conf->getRequestClientConf()->setHost($this->endpoint)
            );
        }
    }
}
