<?php

namespace SALESmanago\Services\Api\V3\Product;

use Exception;
use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Services\Report\ReportService;
use SALESmanago\Entity\ConfigurationInterface as ConfigurationInterfaceV2;
use SALESmanago\Model\Report\ReportModel;

/**
 * Class CatalogServiceWithReporting
 *
 * This class extends the CatalogService to include reporting functionality.
 * It reports actions like creating and deleting catalogs.
 */
class CatalogServiceWithReporting extends CatalogService
{
    /**
     * @var ReportService
     */
    private $ReportingService;

    /**
     * CatalogServiceWithReporting constructor.
     *
     * @param ConfigurationInterface $ConfigurationV3
     * @param ConfigurationInterfaceV2 $ConfigurationV2
     * @param RequestClientConfigurationInterface|null $cUrlClientConf
     * @throws Exception
     */
    public function __construct(
        ConfigurationInterface $ConfigurationV3,
        ConfigurationInterfaceV2 $ConfigurationV2,
        ?RequestClientConfigurationInterface $cUrlClientConf = null
    ) {
        parent::__construct($ConfigurationV3, $cUrlClientConf);
        $this->ReportingService = ReportService::getInstance($ConfigurationV2);
    }

    /**
     * Create a new catalog in SALESmanago and report the action
     *
     * @param CatalogEntityInterface $Catalog
     * @return array
     * @throws ApiV3Exception|Exception
     */
    public function createCatalog(CatalogEntityInterface $Catalog): array
    {
       $response = parent::createCatalog($Catalog);

       $this->ReportingService->reportAction(ReportModel::ACT_CREATE_CATALOG);

       return $response;
    }

    /**
     * Delete a catalog in SALESmanago and report the action
     *
     * @param CatalogEntityInterface $Catalog
     * @return array
     * @throws ApiV3Exception|Exception
     */
    public function delete(CatalogEntityInterface $Catalog): array
    {
        $response = parent::delete($Catalog);

        $this->ReportingService->reportAction(ReportModel::ACT_DELETE_CATALOG);

        return $response;
    }
}