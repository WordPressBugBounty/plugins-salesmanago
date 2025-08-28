<?php

namespace SALESmanago\Model\Api\V3;

use SALESmanago\Entity\Api\V3\Product\Collection\EntityInterface;
use SALESmanago\Entity\Api\V3\Product\Collection\Entity as ProductCollectionEntity;
use SALESmanago\Entity\Api\V3\Product\Collection\EntityInterface as ProductCollectionEntityInterface;
use SALESmanago\Entity\Api\V3\Product\Collection\ItemEntityInterface as ProductCollectionItemEntityInterface;
use SALESmanago\Entity\Contact\ContactInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Model\Api\V3\Product\CollectionModel;
use SALESmanago\Model\Collections\Api\V3\Product\Collection\ItemsCollectionInterface;


/**
 * @deprecated since 3.2.0
 * @see CollectionModel
 */
class ProductCollectionModel
{
    /**
     * Build ProductCollectionEntity from api response;
     *
     * @param array $response
     * @return EntityInterface
     * @throws ApiV3Exception
     */
    public function successResponseToProductCollectionEntity(array $response): EntityInterface
    {
        if (!$response['success'] || empty($response['collectionUUID'])) {
            throw new ApiV3Exception('Failed to create ' . ProductCollectionEntity::class);
        }

        return (new ProductCollectionEntity())
            ->setUuid($response['collectionUUID']);
    }

    /**
     * Build array of product ids from SALEMSmanago API response:
     *
     * @param array $response
     * @return array
     * @throws ApiV3Exception
     */
    public function successResponseToProductsIdsArray(array $response): array
    {
        if (!$response['success'] || empty($response['products'])) {
            throw new ApiV3Exception('Failed to get products');
        }

        return $response['products'];
    }

    /**
     * Build ProductCollectionEntity for create request
     *
     * @param ProductCollectionEntity $productCollectionEntity
     * @return array
     */
    public function buildProductEntityForCreate(ProductCollectionEntity $productCollectionEntity): array
    {
        return array_filter([
            'name' => $productCollectionEntity->getName(),
            'catalogId' => $productCollectionEntity->getProductCatalogId(),
        ], function ($value) {
            return !is_null($value) && $value !== '';
        });
    }

    /**
     * Build ProductCollectionEntity for delete request
     *
     * @param ProductCollectionEntity $productCollectionEntity
     * @return array
     */
    public function buildProductCollectionEntityForDelete(ProductCollectionEntity $productCollectionEntity): array
    {
        return array_filter([
            'collectionUUID' => $productCollectionEntity->getUuid(),
        ], function ($value) {
            return !is_null($value) && $value !== '';
        });
    }

    /**
     * Builds request body for salesmanago add products to product catalogs method
     *
     * @param EntityInterface $productCollection
     * @param ItemsCollectionInterface $itemsCollection
     * @return array
     */
    public function buildAddProductsToProductCollection(
        EntityInterface                      $ProductCollectionEntity,
        ProductCollectionItemEntityInterface $ItemEntity
    ): array
    {
        return [
            'collectionUUID' => $ProductCollectionEntity->getUuid(),
            'data' => [
                [
                    'contactId' => $ItemEntity->getContactId(),
                    'productIds' => $ItemEntity->getProducts()
                ]
            ]
        ];
    }

    /**
     * Build request body to get products for contact from product collection:
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ContactInterface $Contact
     * @return array
     */
    public function buildProductCollectionAndContactForGetProducts(
        ProductCollectionEntityInterface $ProductCollectionEntity,
        ContactInterface $Contact
    ): array
    {
        return [
            'collectionUUID' => $ProductCollectionEntity->getUuid(),
            'contactId' => $Contact->getContactId()
        ];
    }

    /**
     * Build request body for add product by contact to SALESmanago product collection
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ItemsCollectionInterface $itemsCollection
     * @return array
     */
    public function buildProductCollectionAddFromItemsCollection(
        EntityInterface                           $ProductCollectionEntity,
        ItemsCollectionInterface $itemsCollection
    ): array {

        $result = [
            'collectionUUID' => $ProductCollectionEntity->getUuid(),
            'data' => []
        ];

        foreach ($itemsCollection as $ItemEntity) {
            $result['data'][] = [
                'contactId'  => $ItemEntity->getContactId(),
                'productIds' => $ItemEntity->getProducts()
            ];
        }

        return $result;
    }
}