<?php

namespace Tests\Feature\Services\Api\V3;

use SALESmanago\Entity\Api\V3\Product\CustomDetailsEntity;
use SALESmanago\Entity\Api\V3\Product\ProductEntity;
use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Entity\Api\V3\Product\SystemDetailsEntity;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use Faker;

trait ProductTestTrait
{
    /**
     * Generates products
     *
     * @param int $numberOfProductsInProducts
     * @param null $createProductCallback
     * @return ProductsCollection
     */
    protected function createProductsCollection(int $numberOfProductsInProducts = 1, $createProductCallback = null)
    : ProductsCollection
    {
        $ProductsCollection = new ProductsCollection();

        $createProductCallback = ($createProductCallback !== null)
            ? $createProductCallback
            : function() { return $this->createProduct(); };

        while ($numberOfProductsInProducts) {
            $ProductsCollection->addItem(
                is_callable($createProductCallback) ? $createProductCallback() : $createProductCallback
            );
            --$numberOfProductsInProducts;
        }

        return $ProductsCollection;
    }

    /**
     * @return ProductEntityInterface
     */
    protected function createProduct()
    {
        $this->faker = Faker\Factory::create();
        $Product = new ProductEntity();

        //create system details:
        $SystemDetails = $this->createSystemDetails();

        //create custom details:
        $CustomDetails = $this->createCustomDetails();

        $productId = Faker\Factory::create()->uuid();
        $productId = (count_chars($productId) > 32)
            ? substr($productId, 0, 31)
            : $productId;

        $Product
            ->setProductId($productId)
            ->setActive(true)
            ->setAvailable(true)
            ->setCategories($this->faker->words($this->faker->numberBetween(1, 5)))
            ->setCategoryExternalId($this->faker->uuid)
            ->setCustomDetails($CustomDetails)
            ->setDescription(implode(', ', $this->faker->words()))
            ->setDiscountPrice($this->faker->randomNumber())
            ->setProductUrl($this->faker->imageUrl())
            ->setMainImageUrl($this->faker->imageUrl())
            ->setImageUrls($this->createImagesUrls())
            ->setMainCategory($this->faker->words(1)[0])
            ->setName($this->faker->words(1)[0])
            ->setPrice($this->faker->randomNumber())
            ->setUnitPrice($this->faker->randomNumber())
            ->setSystemDetails($SystemDetails)
            ->setQuantity($this->faker->numberBetween(1, 100000));

        return $Product;
    }

    /**
     * @return SystemDetailsEntity
     */
    protected function createSystemDetails()
    {
        $this->faker = Faker\Factory::create();

        $SystemDetails = new SystemDetailsEntity();
        $SystemDetails
            ->setBrand($this->faker->words(1)[0])
            ->setManufacturer($this->faker->words(1)[0])
            ->setPopularity($this->faker->numberBetween(1, 100))
            ->setGender($this->faker->randomKey(['-1', '0', '1', '2', '4']))
            ->setSeason($this->faker->randomKey(['spring', 'summer', 'autumn', 'winter']))
            ->setColor($this->faker->colorName)
            ->setBestseller($this->faker->boolean())
            ->setNewProduct($this->faker->boolean());

        return $SystemDetails;
    }

    /**
     * Creates CustomDetails objects
     * @return CustomDetailsEntity
     */
    protected function createCustomDetails()
    {
        $this->faker = Faker\Factory::create();

        $CustomDetails = new CustomDetailsEntity();
        $numberOfDetails = $this->faker->numberBetween(1, 5);

        while ($numberOfDetails) {
            $CustomDetails->set($this->faker->words(1)[0], $numberOfDetails);
            --$numberOfDetails;
        }

        return $CustomDetails;
    }

    /**
     * Creates images url
     * @return array
     */
    protected function createImagesUrls()
    {
        $this->faker = Faker\Factory::create();

        $imgs = [];
        $cImages = $this->faker->numberBetween(1, 5);

        for ($i=0; $i < $cImages; $i++) {
            $imgs[] = $this->faker->imageUrl();
        }

        return $imgs;
    }
}