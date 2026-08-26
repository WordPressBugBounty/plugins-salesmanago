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
    public function setBrand(?string $brand): SystemDetailsEntity
    {
        $this->brand = (string) $brand;
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
    public function setManufacturer(?string $manufacturer): SystemDetailsEntity
    {
        $this->manufacturer = (string) $manufacturer;
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
    public function setPopularity(?int $popularity): SystemDetailsEntity
    {
        $this->popularity = (integer) $popularity;
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
    public function setGender(?int $gender): SystemDetailsInterface
    {
        $this->gender = (integer) $gender;
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
    public function setSeason(?string $season): SystemDetailsInterface
    {
        $this->season = (string) $season;
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
    public function setColor(?string $color): SystemDetailsInterface
    {
        $this->color = (string) $color;
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
    public function setBestseller(?bool $bestseller): SystemDetailsInterface
    {
        $this->bestseller = (bool) $bestseller;
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
    public function setNewProduct(?bool $newProduct): SystemDetailsInterface
    {
        $this->newProduct = (bool) $newProduct;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = [];

        if (isset($this->brand)) {
            $data['brand'] = (string) $this->brand;
        }

        if (isset($this->manufacturer)) {
            $data['manufacturer'] = (string) $this->manufacturer;
        }

        if (isset($this->popularity)) {
            $data['popularity'] = $this->popularity;
        }

        if (isset($this->gender)) {
            $data['gender'] = $this->gender;
        }

        if (isset($this->season)) {
            $data['season'] = $this->season;
        }

        if (isset($this->color)) {
            $data['color'] = $this->color;
        }

        if (isset($this->bestseller)) {
            $data['bestseller'] = $this->bestseller;
        }

        if (isset($this->newProduct)) {
            $data['newProduct'] = $this->newProduct;
        }

        return $data;
    }
}
