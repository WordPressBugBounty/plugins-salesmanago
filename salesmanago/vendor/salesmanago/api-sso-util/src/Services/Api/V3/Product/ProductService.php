<?php

namespace SALESmanago\Services\Api\V3\Product;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Entity\cUrlClientConfiguration;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\Exception;
use SALESmanago\Helper\Mapper\AdapterInterface;
use SALESmanago\Helper\Mapper\BuilderInterface;
use SALESmanago\Helper\Mapper\ProductEntityBuilder;
use SALESmanago\Model\Api\V3\ProductsModel;
use SALESmanago\Model\Collections\Api\V3\ProductsCollectionInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Services\Api\V3\BasicService;
use SALESmanago\Services\Api\V3\RequestService;

class ProductService extends BasicService
{
    const
        REQUEST_METHOD_POST   = 'POST',
        REQUEST_METHOD_GET    = 'GET',
        API_METHOD_UPSERT     = '/v3/product/upsert',
        API_METHOD_UPDATE_QTY = '/v3/product/updateQuantity',
        API_METHOD_UPDATE_PRICE = '/v3/product/updatePrice';

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
     * Set builder for product entity
     *
     * @param BuilderInterface $builder
     * @return $this
     */
    public function setBuilder(BuilderInterface $builder): ProductService
    {
        $this->ProductsModel->setBuilder($builder);
        return $this;
    }

    /**
     * Upsert products:
     *
     * @param CatalogEntityInterface $Catalog
     * @param ProductsCollectionInterface $ProductsCollection
     * @return array
     * @throws ApiV3Exception
     */
    public function upsertProducts(
        CatalogEntityInterface $Catalog,
        ProductsCollectionInterface $ProductsCollection
    ): array
    {
        //create request body:
        $data = $this->ProductsModel->getProductsToUpsert($Catalog, $ProductsCollection);

        //do request:
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_UPSERT,
            $data
        );
    }

    /**
     * Upsert product with mapping:
     *
     * @param CatalogEntityInterface $Catalog
     * @param mixed $platformProduct
     * @param string $map
     * @param AdapterInterface $adapter
     * @param ProductEntityInterface|null $productEntity
     * @return array
     * @throws ApiV3Exception
     */
    public function upsertProductWithMapping(
        CatalogEntityInterface $Catalog,
        $platformProduct,
        string $map,
        AdapterInterface $adapter,
        ?ProductEntityInterface $productEntity = null
    ): array
    {
        //create request body:
        $data = $this->ProductsModel->getMappedProductToUpsert(
            $Catalog,
            $map,
            $adapter,
            $platformProduct,
            $productEntity
        );

        //do request:
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_UPSERT,
            $data
        );
    }

    /**
     * Upserts products with mapping:
     *
     * @param CatalogEntityInterface $Catalog
     * @param array $platformProductsArray
     * @param string $map
     * @param AdapterInterface $adapter
     * @return array
     * @throws ApiV3Exception
     */
    public function upsertProductsWithMapping(
        CatalogEntityInterface $Catalog,
        array $platformProductsArray,
        string $map,
        AdapterInterface $adapter
    ): array
    {
        //create request body:
        $data = $this->ProductsModel->getMappedProductsCollectionArrayToUpsert(
            $Catalog,
            $platformProductsArray,
            $map,
            $adapter
        );

        //do request:
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_UPSERT,
            $data
        );
    }


    /**
     * Update products quantities
     *
     * @param CatalogEntityInterface $Catalog
     * @param ProductsCollectionInterface $ProductsCollection
     * @return array
     * @throws ApiV3Exception
     */
    public function updateQuantities(
        CatalogEntityInterface $Catalog,
        ProductsCollectionInterface $ProductsCollection
    ) {
        //create request body:
        $data = $this->ProductsModel->getProductsForUpdateQuantities($Catalog, $ProductsCollection);

        //do request:
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_UPDATE_QTY,
            $data
        );
    }

    /**
     * Update product quantity
     *
     * @param CatalogEntityInterface $Catalog
     * @param string|int $productId
     * @param string|int $productQty
     * @return array
     * @throws ApiV3Exception
     */
    public function updateQty(
        CatalogEntityInterface $Catalog,
        $productId,
        $productQty
    ) {
        //create request body:

        $data = $this->ProductsModel->getProductForUpdateQty($Catalog, $productId, $productQty);

        //do request:
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_UPDATE_QTY,
            $data
        );
    }

    /**
     * Update prices
     *
     * @param CatalogEntityInterface $Catalog
     * @param  ProductsCollectionInterface $pricesToUpdate
     * @return array
     * @throws ApiV3Exception
     */
    public function updatePrices(
        CatalogEntityInterface $Catalog,
        ProductsCollectionInterface $pricesToUpdate
    ): array
    {
        //create request body:
        $data = $this->ProductsModel->getProductsForUpdatePrices($Catalog, $pricesToUpdate);

        //do request:
        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_UPDATE_PRICE,
            $data
        );
    }
}
