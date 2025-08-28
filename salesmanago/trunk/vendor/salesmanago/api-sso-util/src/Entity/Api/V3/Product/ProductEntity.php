<?php

namespace SALESmanago\Entity\Api\V3\Product;

use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Entity\DetailsInterface;
use SALESmanago\Helper\DataHelper;

class ProductEntity extends AbstractEntity implements ProductEntityInterface
{
    const DEFAULT_CATEGORY = 'Uncategorized';

    /**
     * @var string - 255 product identifier from your eCommerce platform
     */
    protected $productId;

    /**
     * @var string - 255 product name. You can use diacritics and special characters.
     */
    protected $name;

    /**
     * @var string - 255 category name that can be used in emails and when displaying Recommendation Frames
     */
    protected $mainCategory = self::DEFAULT_CATEGORY;

    /**
     * @var string - 255 category ID used for AI processing and calculating recommendations. If you don’t specify this field, the category ID will be assigned based on the mainCategory field.
     */
    protected $categoryExternalId;

    /**
     * @var array - 5*255 other categories (array)
     */
    protected $categories = [];

    /**
     * @var string - 16384 product description used in emails and custom Recommendation Frames. Most functionalities can use the first 1024 characters only.
     */
    protected $description;

    /**
     * @var string - 2048 product URL that is used to match visits with products. Most functionalities can use the first 512 characters only.
     */
    protected $productUrl;

    /**
     * @var string - 2048 image URL to be used in Recommendation Frames and emails. Most functionalities can use the first 512 characters only.
     */
    protected $mainImageUrl;

    /**
     * @var array - 5*2048	Additional product images that will be used in upcoming features
     */
    protected $imageUrls = [];

    /**
     * @var bool - marks products as temporarily unavailable. This allows you to prevent them from showing up in Recommendation Frames.
     */
    protected $available;

    /**
     * @var bool - marks products as no longer present in your store. This will effectively turn off a given product.
     */
    protected $active;

    /**
     * @var int - available quantity to be used in custom Recommendation Frames
     */
    protected $quantity;

    /**
     * @var float - (19.2) Standard product price
     */
    protected $price;

    /**
     * @var float (19.2) discount product price. You can use this value to display product promo price next to a crossed out standard product price in emails and Recommendation Frames.
     */
    protected $discountPrice;

    /**
     * @var
     *
     * deprecated since 3.6.0
     */
    protected $unitPrice;

    /**
     * @var SystemDetailsInterface
     */
    protected $systemDetails;

    /**
     * @var DetailsInterface
     */
    protected $customDetails;

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMainCategory()
    {
        return $this->mainCategory;
    }

    /**
     * @param mixed $mainCategory
     */
    public function setMainCategory($mainCategory)
    {
        $this->mainCategory = $mainCategory;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryExternalId()
    {
        return $this->categoryExternalId;
    }

    /**
     * @param mixed $categoryExternalId
     */
    public function setCategoryExternalId($categoryExternalId)
    {
        $this->categoryExternalId = $categoryExternalId;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @return ProductEntity
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductUrl()
    {
        return $this->productUrl;
    }

    /**
     * @param mixed $productUrl
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = preg_replace('/([^:])(\/{2,})/', '$1/', $productUrl);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMainImageUrl()
    {
        return $this->mainImageUrl;
    }

    /**
     * @param mixed $mainImageUrl
     */
    public function setMainImageUrl($mainImageUrl)
    {
        $this->mainImageUrl = preg_replace('/([^:])(\/{2,})/', '$1/', $mainImageUrl);
        return $this;
    }

    /**
     * @return array
     */
    public function getImageUrls()
    {
        return $this->imageUrls;
    }

    /**
     * @param array $imageUrls
     * @return ProductEntity
     */
    public function setImageUrls(array $imageUrls)
    {
        $this->imageUrls = $imageUrls;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @param mixed $available
     */
    public function setAvailable($available)
    {
        $this->available = $available;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     * @param mixed $discountPrice
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;
        return $this;
    }

    /**
     * @return mixed
     *
     * deprecated since 3.6.0
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param mixed $unitPrice
     *
     * deprecated since 3.6.0
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    /**
     * @return DetailsInterface
     */
    public function getSystemDetails()
    {
        return !isset($this->systemDetails) ? new SystemDetailsEntity() : $this->systemDetails;
    }

    /**
     * @param mixed $systemDetails
     */
    public function setSystemDetails(SystemDetailsInterface $systemDetails)
    {
        $this->systemDetails = $systemDetails;
        return $this;
    }

    /**
     * @return DetailsInterface
     */
    public function getCustomDetails()
    {
        return !isset($this->customDetails) ? new CustomDetailsEntity() : $this->customDetails;
    }

    /**
     * @param DetailsInterface $customDetails
     * @return ProductEntity
     */
    public function setCustomDetails(DetailsInterface $customDetails)
    {
        $this->customDetails = $customDetails;
        return $this;
    }

    /**
     * @param string $brand
     * @return $this
     */
    public function setBrand(string $brand): ProductEntity
    {
        $this->customDetails->setBrand($brand);
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $systemDetails = !empty($this->systemDetails) ? $this->systemDetails : null;
        $customDetails = !empty($this->customDetails) ? $this->customDetails : null;

        $data = [
            "productId"          => $this->productId,
            "name"               => $this->name,
            "mainCategory"       => empty($this->mainCategory) ? self::DEFAULT_CATEGORY : $this->mainCategory,
            "categoryExternalId" => $this->categoryExternalId,
            "categories"         => $this->categories,
            "description"        => $this->description,
            "productUrl"         => $this->productUrl,
            "mainImageUrl"       => $this->mainImageUrl,
            "imageUrls"          => $this->imageUrls,
            "available"          => $this->available,
            "active"             => $this->active,
            "quantity"           => $this->quantity,
            "price"              => $this->price,
            "discountPrice"      => $this->discountPrice
        ];

        if (!empty($customDetails)) {
            $data['customDetails'] = $customDetails->jsonSerialize();

            if (empty($data['customDetails'])) {
                unset($data['customDetails']);
            }
        }

        $data = DataHelper::filterDataArray($data);

        if (!empty($systemDetails)) {
            $data['systemDetails'] = $systemDetails->jsonSerialize();

            if (empty($data['systemDetails'])) {
                unset($data['systemDetails']);
            }
        }

        $data['setAsNull'] = $this->buildSetAsNull();

        return $data;
    }

	/**
	 * @return array
	 */
	protected function buildSetAsNull(): array
	{
		$setAsNull = [];

		if (empty($this->discountPrice)) {
			$setAsNull[] = ProductEntityInterface::DISCOUNT_PRICE;
		}

		$customDetails = !empty($this->customDetails) ? $this->customDetails : $this->getCustomDetails();

		return array_merge($setAsNull, $customDetails->getEmptyFields());
	}
}
