<?php

namespace SALESmanago\Entity\Api\V3\Product;

use JsonSerializable;

interface SystemDetailsInterface extends JsonSerializable
{
    const BRAND      = 'brand',
        MANUFACTURER = 'manufacturer',
        POPULARITY   = 'popularity',
        GENDER       = 'gender',
        SEASON       = 'season',
        COLOR        = 'color',
        BESTSELLER   = 'bestseller',
        NEW_PRODUCT  = 'newProduct';


    /**
     * @return string|null
     */
    public function getBrand();

    /**
     * @param string $brand
     * @return SystemDetailsInterface
     */
    public function setBrand(?string $brand): SystemDetailsEntity;

    /**
     * @return string|null
     */
    public function getManufacturer();

    /**
     * @param string $manufacturer
     * @return SystemDetailsInterface
     */
    public function setManufacturer(?string $manufacturer): SystemDetailsEntity;

    /**
     * @return int|null
     */
    public function getPopularity();

    /**
     * @param int $popularity
     * @return SystemDetailsInterface
     */
    public function setPopularity(?int $popularity): SystemDetailsEntity;

    /**
     * @return int
     */
    public function getGender();

    /**
     * @param int|null $gender
     * @return SystemDetailsInterface
     */
    public function setGender(?int $gender): SystemDetailsInterface;

    /**
     * @return string|string
     */
    public function getSeason();

    /**
     * @param string $season
     */
    public function setSeason(?string $season): SystemDetailsInterface;

    /**
     * @return string|null
     */
    public function getColor();

    /**
     * @param string $color
     * @return SystemDetailsInterface
     */
    public function setColor(?string $color): SystemDetailsInterface;

    /**
     * @return bool
     */
    public function isBestseller();

    /**
     * @param bool $bestseller
     * @return SystemDetailsInterface
     */
    public function setBestseller(?bool $bestseller): SystemDetailsInterface;

    /**
     * @return bool
     */
    public function isNewProduct();

    /**
     * @param bool $newProduct
     * @return SystemDetailsInterface
     */
    public function setNewProduct(?bool $newProduct): SystemDetailsInterface;
}
