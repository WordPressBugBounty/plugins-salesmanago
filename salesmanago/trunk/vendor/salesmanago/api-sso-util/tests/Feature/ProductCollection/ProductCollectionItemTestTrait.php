<?php

namespace Tests\Feature\ProductCollection;

use SALESmanago\Entity\Api\V3\Product\Collection\ItemEntity;
use Faker\Factory;
use SALESmanago\Entity\Api\V3\Product\Collection\ItemEntityInterface;
use SALESmanago\Exception\Exception;
use Tests\Feature\Services\Api\V3\ProductTestTrait;
use Tests\Feature\User\Contact\ContactTestTrait;

trait ProductCollectionItemTestTrait
{
    use ProductTestTrait;
    use ContactTestTrait;

    /**
     * Generates ProductCatalog dummy Item for test purpose:
     *
     * @return ItemEntityInterface
     * @throws Exception
     */
    public function generateDummyItemEntity()
    {
        //create collection of product entities:
        $productsCollection = $this->createProductsCollection(
            Factory::create()->numberBetween(1, 100),
            function() { return $this->createProduct(); }
        );

        //upsert/create contact in/to SALESmanago application
        $Contact = $this->createDummyContactInSalesmanago();

        //create and return ProductCollection item:
        return (new ItemEntity())
            ->setProducts($productsCollection)
            ->setContactId($Contact->getContactId());
    }
}