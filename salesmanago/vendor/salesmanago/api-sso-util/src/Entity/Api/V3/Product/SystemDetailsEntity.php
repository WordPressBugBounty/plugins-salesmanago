<?php

namespace SALESmanago\Entity\Api\V3\Product;

use SALESmanago\Helper\DataHelper;

class SystemDetailsEntity implements SystemDetailsInterface
{
    /**
     * @var string - 255 standard product details
     */
    protected $brand;

    /**
     * @var string - 255 standard product details
     */
    protected $manufacturer;

    /**
     * @var int - integer value to mark how popular the upserted product is, for example, using a range 1-100.
     */
    protected $popularity;

    /**
     * @var int - enum to identify the gender the product is designed for: -1 – undefined, 0 – female, 1 – male, 1 – male, 2 – other, 4 – unisex
     */
    protected $gender;

    /**
     * @var string - 255 standard product details
     */
    protected $season;

    /**
     * @var string - 25 standard product details
     */
    protected $color;

    /**
     * @var bool - flags that you can use in emails and custom Recommendation Frames
     */
    protected $bestseller;

    /**
     * @var bool - flags that you can use in emails and custom Recommendation Frames
     */
    protected $newProduct;

    /**
     * @inheritDoc
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @inheritDoc
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @inheritDoc
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPopularity()
    {
        return $this->popularity;
    }

    /**
     * @inheritDoc
     */
    public function setPopularity($popularity)
    {
        $this->popularity = $popularity;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @inheritDoc
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @inheritDoc
     */
    public function setSeason($season)
    {
        $this->season = $season;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @inheritDoc
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isBestseller()
    {
        return $this->bestseller;
    }

    /**
     * @inheritDoc
     */
    public function setBestseller($bestseller)
    {
        $this->bestseller = $bestseller;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isNewProduct()
    {
        return $this->newProduct;
    }

    /**
     * @inheritDoc
     */
    public function setNewProduct($newProduct)
    {
        $this->newProduct = $newProduct;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return DataHelper::filterDataArray([
            "brand"        => $this->brand,
            "manufacturer" => $this->manufacturer,
            "popularity"   => $this->popularity,
            "gender"       => $this->gender,
            "season"       => $this->season,
            "color"        => $this->color,
            "bestseller"   => $this->bestseller,
            "newProduct"   => $this->newProduct
        ]);
    }
}