<?php

namespace bhr\Admin\Builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Adapter\BasicProductDetailsAdapter;
use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\Helper;
use bhr\Frontend\Plugins\Wc\WcEventModel;
use bhr\Includes\Helper as IncludesHelper;
use Error;
use Exception;
use SALESmanago\Entity\Api\V3\Product\ProductEntity;
use SALESmanago\Exception\Exception as SmException;
use SALESmanago\Helper\Mapper\Map\ItemEntity;
use SALESmanago\Helper\Mapper\ProductEntityBuilder;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection as Collection;
use WC_Product_Variable;

/**
 * ProductBuilder class
 * Build product entities for export and hook upsert
 */
class ProductBuilder {

	const PRODUCT_INSTOCK                = 'instock',
		  PRODUCT_AVAILABLE_ON_BACKORDER = 'onbackorder',
		  PRODUCT_OUTOFSTOCK             = 'outofstock',
		  PRODUCT_INACTIVE               = 'hidden';

    /*
     * @var AdminModel
     */
    private AdminModel $AdminModel;

	/**
	 * ProductBuilder Constructor
	 * Make sure that WooCommerce functions are available.
	 */
	public function __construct( ?AdminModel $AdminModel = null ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			Helper::loadSMPluginLast();
		}
        if ( $AdminModel ) {
            $this->AdminModel = $AdminModel;
        }
	}

	/**
	 * Gets wc_product data
	 *
	 * @param  string  $product_id  Product id.
	 * @param $product_identifier_type
	 * @param  array  $parentImageUrls
	 * @param  bool  $webhook
	 *
	 * @return array|null
	 */
	protected function get_wc_product_data( $product_id, $product_identifier_type, $parentImageUrls = [], $webhook = true ) {
		try {
			$wc_product = wc_get_product( $product_id );

			// productId becomes sku if product_identifier_type is set to Sku
			// productId becomes id if product_identifier_type is set to Product ID or Variant ID (IDs are unique per product)
			if ($product_identifier_type == WcEventModel::PRODUCT_IDENTIFIER_TYPE_SKU) {
				$wc_product_data['productId'] = $wc_product->get_sku();
			} else {
				$wc_product_data['productId'] = $product_id;
			}

			$wc_product_data['name']          = $wc_product->get_name();
			$wc_product_data['description']   = $wc_product->get_short_description() ?? $wc_product->get_description() ?? '';
			$wc_product_data['stock_status']  = isset( $wc_product->get_data()['stock_status'] ) ? $wc_product->get_data()['stock_status'] : '';
			$wc_product_data['price']         = $wc_product->get_regular_price() ?: $wc_product->get_price();
			$wc_product_data['discountPrice'] = $wc_product->get_sale_price() ?: $wc_product->get_price();
			$wc_product_data['attributes']    = $wc_product->is_type('variation')
				? $wc_product->get_attributes()
				: $this->getAttributesFromOptions($wc_product->get_attributes());
			$wc_prod_categories = get_the_terms( $product_id, 'product_cat' );
			if ( ! $wc_prod_categories && $wc_product->get_parent_id() ) {
				$wc_prod_categories = get_the_terms( $wc_product->get_parent_id(), 'product_cat' );
			}

			if ( $wc_prod_categories ) {
				$no_of_categories = min(count($wc_prod_categories), 5);
				for ( $i = 0; $i < $no_of_categories;  $i++ ) {
					if ( 0 === $i ) { // Set the first category as mainCategory.
						$wc_product_data['mainCategory']       = ! empty( $wc_prod_categories[0]->name ) ? $wc_prod_categories[0]->name : '';
						$wc_product_data['categoryExternalId'] = ! empty( $wc_prod_categories[0]->term_taxonomy_id ) ? $wc_prod_categories[0]->term_taxonomy_id : '';
					}
					$wc_product_data['categories'][] = ! empty( $wc_prod_categories[ $i ]->name ) ? $wc_prod_categories[ $i ]->name : '';
				}
			} else {
				$wc_product_data['categories'] = '';
			}
			$quantity                        = $wc_product->get_stock_quantity();
			$wc_product_data['quantity']     = ! is_null( $quantity ) ? $quantity : '';
			$wc_product_data['mainImageUrl'] = ! empty( $wc_product->get_image() ) ? IncludesHelper::getImageUrl( $wc_product->get_image_id() ) : '';
			$wc_product_data['productUrl']   = ! empty( $wc_product->get_permalink() ) ? $wc_product->get_permalink() : '';
			$wc_product_data['active']       = ! ( $wc_product->get_catalog_visibility() === self::PRODUCT_INACTIVE );
			$wc_product_data['available']    = $this->determine_product_availability( $wc_product );

			if ($webhook) {
				// live synchro case
				$imageUrls = !empty($parentImageUrls)
					? $parentImageUrls
					: (array_map([IncludesHelper::class, 'getImageUrl'], $wc_product->get_gallery_image_ids() ?? []));
			} else {
				// export case
				$imageUrls = !empty($parentImageUrls)
					? (array_map([IncludesHelper::class, 'getImageUrl'], $parentImageUrls))
					: (array_map([IncludesHelper::class, 'getImageUrl'], $wc_product->get_gallery_image_ids() ?? []));
			}

			$wc_product_data['imageUrls'] = array_slice($imageUrls, 0, 5);

			return $wc_product_data;
		} catch ( Exception | Error $e ) {
			Helper::salesmanago_log( $e->getMessage(), __FILE__ );
			return null;
		}
	}

    /**
     * @param array $attributes
     *
     * @return array
     */
    private function getAttributesFromOptions( array $attributes ) {
        $result = array();

        foreach ($attributes as $key => $object ) {
            if ( is_object( $object ) && property_exists( $object, 'data' ) ) {
                $data = $object->get_data();
                if ( isset( $data[ 'options' ] ) ) {
                    $names = array();

                    foreach ( $data[ 'options' ] as $optionId ) {
                        if ( $data[ 'id' ] > 0) {
                            // if id > 0 then there is no attribute name in array, we need to fetch it from terms
                            $term = get_term( $optionId );
                            if ( $term && !is_wp_error( $term ) ) {
                                $names[] = $term->name;
                            }
                        } else {
                            // else this is custom attribute so we have its name in array
                            $names[] = $optionId;
                        }
                    }

                    $result[ $data[ 'name' ] ] = implode( ', ', $names );
                }
            }
        }

        return $result;
    }

	/**
	 * Transform array to Products Collection (product export)
	 *
	 * @param array $products Products.
	 * @param string $product_identifier_type Product identifier type.
	 * @return Collection
	 * @throws SmException SALESmanago exception.
	 */
	public function add_products_to_collection( $products, $product_identifier_type ) {
		try {
			$parentImageUrls = [];
			$childrens = [];

			$products_collection = new Collection();
			foreach ( $products as $product ) {
				if ( empty( $product['productId'] )
				     || ( empty( $product['_sku'] ) && $product_identifier_type == WcEventModel::PRODUCT_IDENTIFIER_TYPE_SKU )
				) {
					continue;
				}

				$wc_product = wc_get_product( $product['productId'] );

				if ($wc_product->is_type('variable') && !empty($wc_product->get_children())) {
					$childrens = $wc_product->get_children();
					$parentImageUrls = $wc_product->get_gallery_image_ids();
				}

				if (in_array($product['productId'], $childrens)) {
					$wc_product_data = $this->get_wc_product_data( $product['productId'], $product_identifier_type, $parentImageUrls, false );
				} else {
					$wc_product_data = $this->get_wc_product_data( $product['productId'], $product_identifier_type, [], false );
				}

				if ( ! $this->is_product_valid( $wc_product_data ) ) {
					continue;
				}

				$product_entity = $this->build_product_entity( $wc_product_data );

				$BasicProductDetailsAdapter = new BasicProductDetailsAdapter();

				$smProduct = ( new ProductEntityBuilder() )->build (
					$product_entity,
					$this->prepareMap( $wc_product_data[ 'attributes' ] ),
					$BasicProductDetailsAdapter,
					$wc_product_data
				);

				$products_collection->addItem( $smProduct );
			}
			return $products_collection;
		} catch ( Exception | Error $e ) {
			throw new SmException( 'Invalid product. ID:' . $product['productId'] );
		}
	}

	/**
	 * Transform product id to Products Collection (product update by hook)
	 *
	 * @param  string $product_id  Product ID.
	 * @param  string  $product_identifier_type  Product identifier type.
	 * @param  null|Collection $products_collection Product collection.
	 * @return Collection
	 * @throws SmException SALESmanago exception.
	 */
	public function add_product_to_collection( $product_id, $product_identifier_type, $products_collection = null, $parentImageUrls = [] ) {
		try {
			if ( is_null( $products_collection ) ) {
				$products_collection = new Collection();
			}
			if ( ! empty( $product_id ) ) {
				$wc_product_data = $this->get_wc_product_data( $product_id, $product_identifier_type, $parentImageUrls, true );
				if ( $this->is_product_valid( $wc_product_data ) ) {
					$product_entity = $this->build_product_entity( $wc_product_data );

					$BasicProductDetailsAdapter = new BasicProductDetailsAdapter();

					$smProduct = ( new ProductEntityBuilder() )->build (
						$product_entity,
						$this->prepareMap( $wc_product_data['attributes'] ),
						$BasicProductDetailsAdapter,
						$wc_product_data
					);

					$products_collection->addItem( $smProduct );
				}
			}
			return $products_collection;
		} catch ( Exception | Error $e ) {
			throw new SmException( 'Invalid product. ID:' . $product_id );
		}
	}

	/**
	 * Builds product entity from wc product array
	 *
	 * @param array $wc_product_data WC product data.
	 * @return ProductEntity Product entity
	 */
	protected function build_product_entity( $wc_product_data ) {
		$product_entity = new ProductEntity();
		$product_entity
			->setProductId( $wc_product_data['productId'] )
			->setName( trim( $wc_product_data['name'] ) )
			->setDescription( trim( wp_strip_all_tags( $wc_product_data['description'] ) ) )
			->setAvailable( $wc_product_data['available'] )
			->setMainCategory( trim( $wc_product_data['mainCategory'] ) )
			->setCategoryExternalId( (int) $wc_product_data['categoryExternalId'] )
			->setProductUrl( $wc_product_data['productUrl'] )
			->setActive( $wc_product_data['active'] )
			->setMainImageUrl( $wc_product_data['mainImageUrl'] )
		    ->setImageUrls( $wc_product_data['imageUrls'] );
		if ( is_numeric( $wc_product_data['price'] ) ) {
			$product_entity->setPrice( round( $wc_product_data['price'], 2 ) );
		}
		if ( is_numeric( $wc_product_data['discountPrice'] ) ) {
			$product_entity->setDiscountPrice( round( $wc_product_data['discountPrice'], 2 ) );
		}
		if ( ! empty( $wc_product_data['categories'] ) ) {
			$product_entity->setCategories( $wc_product_data['categories'] );
		}
		return $product_entity;
	}

	/**
	 * Determine product availability based on its quantity and stock_status
	 *
	 * 1. If there's no quantity (is null) check the stock status, stock_status != outofstock -> product available
	 * 2. If quantity is set:
	 *      a) qty > 0 -> product available BUT:
	 *          i. The admin could have changed the stock_status to outofstock -> product not available
	 *      b) qty <= 0 -> product not available BUT:
	 *          i. availability on backorder enabled? -> product available
	 *
	 * @param WC_Product_Variable $wc_product  WooCommerce product.
	 *
	 * @return bool isAvailable
	 */
	private function determine_product_availability( $wc_product ) {
		$quantity     = $wc_product->get_stock_quantity();
		$stock_status = $wc_product->get_stock_status();

		return is_null( $quantity ) ?
			self::PRODUCT_OUTOFSTOCK !== $stock_status :
			( $quantity > 0 ?
				! ( self::PRODUCT_OUTOFSTOCK === $stock_status ) :
				self::PRODUCT_AVAILABLE_ON_BACKORDER === $stock_status );
	}

	/**
	 * Check if product data array has required values - eliminates empty/invalid products
	 *
	 * @param array $wc_product_data Product data.
	 *
	 * @return boolean isValid
	 */
	private function is_product_valid( $wc_product_data ) {
		if ( empty( $wc_product_data['name'] ) || empty( $wc_product_data['price'] ) ) {
			return false;
		}
		return true;
	}

    /**
     * @param array $attributes
     *
     * @return string|null
     */
    private function prepareMap( array $attributes ) {
        $mapObject = array();
        $mapFromPlatformSettings = $this->AdminModel->getPlatformSettings()->getDetailsMapping();

        foreach ( $attributes as $name => $value ) {
            $cleanedName = str_replace('pa_', '', $name );
            $mapObject[ $cleanedName ] = [
                'name' => $name,
                'value' => $value,
                'label' => $cleanedName,
            ];
        }

        $map = array_map(
            fn( $value ) => $mapObject[ $value ] ?? null,
            $mapFromPlatformSettings
        );

        if ( empty( $map ) ) {
            $map = IncludesHelper::generateDefaultMapping();
        }

        return json_encode( $map );
    }
}
