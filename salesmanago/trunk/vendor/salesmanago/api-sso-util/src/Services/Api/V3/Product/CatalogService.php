<?php

namespace SALESmanago\Services\Api\V3\Product;

use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Api\V3\CatalogModel;
use SALESmanago\Services\Api\V3\BasicService;

class CatalogService extends BasicService
{
    const
        API_METHOD_LIST     = '/v3/product/catalogList',
        API_METHOD_CREATE   = '/v3/product/catalogUpsert',
        API_METHOD_DELETE   = '/v3/product/catalogDelete';

    /**
     * @var CatalogModel
     */
    protected $catalogModel;

    /**
     * @param ConfigurationInterface $ConfigurationV3
     * @param RequestClientConfigurationInterface|null $cUrlClientConf
     */
    public function __construct(
        ConfigurationInterface $ConfigurationV3,
        RequestClientConfigurationInterface $cUrlClientConf = null
    ) {
        $this->catalogModel = new CatalogModel();
        parent::__construct($ConfigurationV3, $cUrlClientConf);
    }

    /**
     * Get/list catalogs from SALESmanago
     *
     * @return array
     * @throws ApiV3Exception|Exception
     */
    public function getCatalogs()
    {
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_GET,
            self::API_METHOD_LIST
        );

        $catalogs = [];

        if (!empty($response['catalogs'])) {
            foreach ($response['catalogs'] as $catalog) {
                $catalogs[] = new CatalogEntity($catalog);
            }
        }

        return $catalogs;
    }

    /**
     * Return catalogs limits for vendor
     *
     * @return int|null
     * @throws ApiV3Exception
     */
    public function getLimit()
    {
        //same API method as for getCatalogs():
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_GET,
            self::API_METHOD_LIST
        );

        if (empty($response['limits'])) {
            return null;
        }

        return !empty($response['limits']['max']) ? $response['limits']['max'] : null;
    }

    /**
     * Calculates and return possible amount of product catalogs to create
     *
     * @return int
     * @throws ApiV3Exception
     */
    public function getAmountAvailableToCreate(): int
    {
        //same API method as for getCatalogs():
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_GET,
            self::API_METHOD_LIST
        );

        if (empty($response['limits'])) {
            return 0;
        }

        $maxLimit = !empty($response['limits']['max']) ? $response['limits']['max'] : null;

        if (empty($response['catalogs'])) {
            return $maxLimit;
        }

        $availableCatalogs = count($response['catalogs']);

        return ($maxLimit - $availableCatalogs);
    }

    /**
     * Create catalog in SALESmanago
     *
     * @param CatalogEntityInterface $Catalog
     * @return array
     * @throws ApiV3Exception
     */
    public function createCatalog(CatalogEntityInterface $Catalog)
    {
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_CREATE,
            $Catalog //will be encoded to json further in request service
        );
    }

    /**
     * Remove Catalog from SALESmanago
     *
     * @param CatalogEntityInterface $Catalog
     * @return array
     * @throws ApiV3Exception
     */
    public function delete(CatalogEntityInterface $Catalog)
    {
        $data = $this->catalogModel->buildForDelete($Catalog);
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_DELETE,
            $data
        );
    }
}