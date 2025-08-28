<?php

namespace SALESmanago\Entity\Api\V3\Product;

use \JsonSerializable;
use SALESmanago\Entity\DetailsInterface;

interface ProductEntityInterface extends JsonSerializable
{
    const DISCOUNT_PRICE = 'discountPrice';

    /**
     * @return ProductEntityInterface
     */
    public function getProductId();

    /**
     * @param mixed $productId
     * @return ProductEntityInterface
     */
    public function setProductId($productId);

    /**
     * @return ProductEntityInterface
     */
    public function getName();

    /**
     * @param mixed $name
     * @return ProductEntityInterface
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getMainCategory();

    /**
     * @param mixed $mainCategory
     * @return ProductEntityInterface
     */
    public function setMainCategory($mainCategory);

    /**
     * @return mixed
     */
    public function getCategoryExternalId();

    /**
     * @param mixed $categoryExternalId
     * @return ProductEntityInterface
     */
    public function setCategoryExternalId($categoryExternalId);

    /**
     * @return array
     */
    public function getCategories();

    /**
     * @param array $categories
     * @return ProductEntityInterface
     */
    public function setCategories(array $categories);

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param mixed $description
     * @return ProductEntityInterface
     */
    public function setDescription($description);

    /**
     * @return mixed
     */
    public function getProductUrl();

    /**
     * @param mixed $productUrl
     * @return ProductEntityInterface
     */
    public function setProductUrl($productUrl);

    /**
     * @return mixed
     */
    public function getMainImageUrl();

    /**
     * @param mixed $mainImageUrl
     * @return ProductEntityInterface
     */
    public function setMainImageUrl($mainImageUrl);

    /**
     * @return array
     */
    public function getImageUrls();

    /**
     * @param array $imageUrls
     * @return ProductEntityInterface
     */
    public function setImageUrls(array $imageUrls);

    /**
     * @return mixed
     */
    public function getAvailable();

    /**
     * @param mixed $available
     * @return ProductEntityInterface
     */
    public function setAvailable($available);

    /**
     * @return mixed
     */
    public function getActive();

    /**
     * @param mixed $active
     * @return ProductEntityInterface
     */
    public function setActive($active);

    /**
     * @return mixed
     */
    public function getQuantity();

    /**
     * @param mixed $quantity
     * @return ProductEntityInterface
     */
    public function setQuantity($quantity);

    /**
     * @return mixed
     */
    public function getPrice();

    /**
     * @param mixed $price
     * @return ProductEntityInterface
     */
    public function setPrice($price);

    /**
     * @return mixed
     */
    public function getDiscountPrice();

    /**
     * @param mixed $discountPrice
     * @return ProductEntityInterface
     */
    public function setDiscountPrice($discountPrice);

    /**
     * @return mixed
     *
     * deprecated since 3.6.0
     */
    public function getUnitPrice();

    /**
     * @param mixed $unitPrice
     * @return ProductEntityInterface
     *
     * deprecated since 3.6.0
     */
    public function setUnitPrice($unitPrice);

    /**
     * @return mixed
     */
    public function getSystemDetails();

    /**
     * @param mixed $systemDetails
     * @return ProductEntityInterface
     */
    public function setSystemDetails(SystemDetailsInterface $systemDetails);

    /**
     * @return mixed
     */
    public function getCustomDetails();

    /**
     * @param DetailsInterface $customDetails
     * @return ProductEntityInterface
     */
    public function setCustomDetails(DetailsInterface $customDetails);
}