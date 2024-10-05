<?php

namespace SALESmanago\Entity\Api\V3\Product;

use JsonSerializable;

interface SystemDetailsInterface extends JsonSerializable
{
    /**
     * @return string|null
     */
    public function getBrand();

    /**
     * @param string $brand
     * @return SystemDetailsInterface
     */
    public function setBrand($brand);

    /**
     * @return string|null
     */
    public function getManufacturer();

    /**
     * @param string $manufacturer
     * @return SystemDetailsInterface
     */
    public function setManufacturer($manufacturer);

    /**
     * @return int|null
     */
    public function getPopularity();

    /**
     * @param int $popularity
     * @return SystemDetailsInterface
     */
    public function setPopularity($popularity);

    /**
     * @return int
     */
    public function getGender();

    /**
     * @param int|null $gender
     * @return SystemDetailsInterface
     */
    public function setGender($gender);

    /**
     * @return string|string
     */
    public function getSeason();

    /**
     * @param string $season
     */
    public function setSeason($season);

    /**
     * @return string|null
     */
    public function getColor();

    /**
     * @param string $color
     * @return SystemDetailsInterface
     */
    public function setColor($color);

    /**
     * @return bool
     */
    public function isBestseller();

    /**
     * @param bool $bestseller
     * @return SystemDetailsInterface
     */
    public function setBestseller($bestseller);

    /**
     * @return bool
     */
    public function isNewProduct();

    /**
     * @param bool $newProduct
     * @return SystemDetailsInterface
     */
    public function setNewProduct($newProduct);
}