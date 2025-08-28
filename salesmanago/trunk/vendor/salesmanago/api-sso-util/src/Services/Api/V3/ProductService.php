<?php

namespace SALESmanago\Services\Api\V3;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\cUrlClientConfiguration;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Api\V3\ProductsModel;
use SALESmanago\Model\Collections\Api\V3\ProductsCollectionInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Services\Api\V3\BasicService;

/**
 * @deprecated since 3.2.0
 * @see \SALESmanago\Services\Api\V3\Product\ProductService
 */
class ProductService extends BasicService
{
    const
        REQUEST_METHOD_POST = 'POST',
        REQUEST_METHOD_GET  = 'GET',
        API_METHOD_UPSERT   = '/v3/product/upsert';

    /**
     * @var RequestService
     */
    protected $RequestService;

    /**
     * @var ProductsModel
     */
    protected $ProductsModel;

    /**
     * @throws Exception
     */
    public function __construct(
        ConfigurationInterface $ConfigurationV3,
        RequestClientConfigurationInterface $cUrlClientConf = null
    ) {
        parent::__construct(
            $ConfigurationV3,
            $cUrlClientConf
        );

        $this->ProductsModel = new ProductsModel();
    }

    /**
     * @param CatalogEntityInterface $Catalog
     * @param ProductsCollectionInterface $ProductsCollection
     * @return array
     * @throws ApiV3Exception
     */
    public function upsertProducts(
        CatalogEntityInterface $Catalog,
        ProductsCollectionInterface $ProductsCollection
    ) {
        $data = $this->ProductsModel->getProductsToUpsert($Catalog, $ProductsCollection);

        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_UPSERT,
            $data
        );
    }
}