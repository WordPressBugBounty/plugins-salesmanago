<?php

namespace SALESmanago\Services\Api\V3\Product;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\Api\V3\Product\Collection\Entity;
use SALESmanago\Entity\Api\V3\Product\Collection\EntityInterface as ProductCollectionEntityInterface;
use SALESmanago\Entity\Api\V3\Product\Collection\ItemEntityInterface as ProductCollectionItemEntityInterface;
use SALESmanago\Entity\Contact\ContactInterface;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Model\Api\V3\Product\CollectionModel as ProductCollectionModel;
use SALESmanago\Model\Collections\Api\V3\Product\Collection\ItemsCollectionInterface;
use SALESmanago\Services\Api\V3\BasicService;

class CollectionService extends BasicService
{
    public const
        REQUEST_METHOD_GET  = 'GET',
        REQUEST_METHOD_POST = 'POST',
        API_METHOD_ADD    = '/v3/product/collection/add',
        API_METHOD_DELETE = '/v3/product/collection/delete',
        API_METHOD_PRODUCTS_ADD     = '/v3/product/collection/products/add',
        API_METHOD_CONTACT_PRODUCTS = '/v3/product/collection/contact/products',
        API_METHOD_PRODUCTS_DELETE  = '/v3/product/collection/products/delete',
        API_METHOD_COLLECTIONS_LIST = '/v3/product/collection/list';

    /**
     * @var ProductCollectionModel
     */
    protected $productCollectionModel;

    /**
     * @var int limit of Product Collection for SM account
     */
    protected $collectionLimit;

    /**
     * @param ConfigurationInterface $ConfigurationV3
     * @param RequestClientConfigurationInterface|null $cUrlClientConf
     */
    public function __construct(
        ConfigurationInterface $ConfigurationV3,
        RequestClientConfigurationInterface $cUrlClientConf = null
    )  {
        parent::__construct($ConfigurationV3, $cUrlClientConf);
        $this->productCollectionModel = new ProductCollectionModel();
    }

    /**
     * Create new product collection in SALESmanago
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param CatalogEntityInterface $catalogEntity
     * @return ProductCollectionEntityInterface

     * @throws ApiV3Exception
     */
    public function add(
        ProductCollectionEntityInterface
        $ProductCollectionEntity,
        CatalogEntityInterface $catalogEntity
    ): ProductCollectionEntityInterface
    {
        //hang product collection to product catalog:
        $ProductCollectionEntity->setProductCatalogId($catalogEntity->getId());

        //build for create request:
        $productCollectionArr = $this->productCollectionModel->buildProductEntityForCreate($ProductCollectionEntity);

        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_ADD,
            $productCollectionArr
        );

        //return successfully created entity:
        return $this->productCollectionModel->successResponseToProductCollectionEntity($response);
    }

    /**
     * Remove product collection from salesmanago
     *
     * @param ProductCollectionEntityInterface $productCollectionEntity
     * @return bool
     * @throws ApiV3Exception
     */
    public function delete(ProductCollectionEntityInterface $productCollectionEntity): bool
    {
        //build for create request:
        $productCollectionArr = $this->productCollectionModel
            ->buildProductCollectionEntityForDelete($productCollectionEntity);

        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_DELETE,
            $productCollectionArr
        );

        return $response['success'];
    }

    /**
     * Get collections list form SM app
     *
     * @return array
     * @throws ApiV3Exception
     */
    public function list(): array {
        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_GET,
            self::API_METHOD_COLLECTIONS_LIST
        );

        //set limits for collection
        $this->collectionLimit = $response['limits']['max'];

        if (empty($response['collections'])) {
            //return empty array:
            return [];
        }

        $collections = [];

        foreach ($response['collections'] as $collection) {
            $collections[] = (new Entity())
                ->setName($collection['name'])
                ->setUuid($collection['collectionId']);
        }

        //return collections array:
        return $collections;
    }

    /**
     * Get limits for catalog
     *
     * @return int
     * @throws ApiV3Exception
     */
    public function getLimit(): int
    {
        //list should set collectionLimit:
        $this->list();

        return $this->collectionLimit;
    }

    /**
     * Returns amount of product collection which could be created by vendor in SM
     *
     * @return int
     * @throws ApiV3Exception
     */
    public function getAmountAvailableToCreate(): int
    {
        $listOfCollections = $this->list();//should also set limit

        return $this->collectionLimit - count($listOfCollections);
    }

    /**
     * Add products to SALESmanago Products Collection through API
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ProductCollectionItemEntityInterface $ItemEntity
     * @return void
     * @throws ApiV3Exception
     */
    public function addProducts(
        ProductCollectionEntityInterface $ProductCollectionEntity,
        ProductCollectionItemEntityInterface $ItemEntity
    ): bool {
        //build request body array:
        $data = $this->productCollectionModel
            ->buildAddProductsToProductCollection($ProductCollectionEntity, $ItemEntity);

        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_PRODUCTS_ADD,
            $data
        );

        return $response['success'];
    }

    /**
     * Remove products for specific contact form Salesmanago Product Collection
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ProductCollectionItemEntityInterface $ItemEntity
     * @return bool
     * @throws ApiV3Exception
     */
    public function deleteProducts(
        ProductCollectionEntityInterface $ProductCollectionEntity,
        ProductCollectionItemEntityInterface $ItemEntity
    ): bool
    {
        //build request body array:
        $data = $this->productCollectionModel
            ->buildAddProductsToProductCollection($ProductCollectionEntity, $ItemEntity);

        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_PRODUCTS_DELETE,
            $data
        );

        return $response['success'];
    }

    /**
     * Get products from product collection by contact
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ContactInterface $Contact
     * @return array - of product ids in collection for contact
     * @throws ApiV3Exception
     */
    public function getProducts(
        ProductCollectionEntityInterface $ProductCollectionEntity,
        ContactInterface $Contact
    ): array
    {
        //build request body:
        $data = $this->productCollectionModel->buildProductCollectionAndContactForGetProducts(
            $ProductCollectionEntity,
            $Contact
        );

        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_CONTACT_PRODUCTS,
            $data
        );

        //build response:
        return $this->productCollectionModel->successResponseToProductsIdsArray($response);
    }

    /**
     * Batch add products for contacts to SALESmanago product collection
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ItemsCollectionInterface $ItemsCollection
     * @return bool
     * @throws ApiV3Exception
     */
    public function addItemsCollection(
        ProductCollectionEntityInterface $ProductCollectionEntity,
        ItemsCollectionInterface $ItemsCollection
    ): bool
    {
        //build request body array:
        $data = $this->productCollectionModel
            ->buildProductCollectionAddFromItemsCollection($ProductCollectionEntity, $ItemsCollection);

        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_PRODUCTS_ADD,
            $data
        );

        return $response['success'];
    }

    /**
     * Batch remove products for contacts from SALESmanago product collection
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ItemsCollectionInterface $ItemsCollection
     * @return bool
     * @throws ApiV3Exception
     */
    public function removeItemsCollection(
        ProductCollectionEntityInterface $ProductCollectionEntity,
        ItemsCollectionInterface $ItemsCollection
    ): bool
    {
        //build request body array:
        $data = $this->productCollectionModel
            ->buildProductCollectionAddFromItemsCollection($ProductCollectionEntity, $ItemsCollection);

        //request to api:
        $response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_PRODUCTS_DELETE,
            $data
        );

        return $response['success'];
    }
}