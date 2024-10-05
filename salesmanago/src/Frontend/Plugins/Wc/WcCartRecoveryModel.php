<?php


namespace bhr\Frontend\Plugins\Wc;

use bhr\Frontend\Model\AbstractModel;
use bhr\Frontend\Model\Helper;

class WcCartRecoveryModel extends AbstractModel
{
    const DELIMITER = '|';

	/**
	 * This method is used in Admin/RestApi and recover cart from link
	 *
	 * @param $rawData
	 */
    public static function recoverCart($rawData)
    {
        /* Note:
        To fit as much data to URL as possible serialization is done with one delimiter (| - self::DELIMITER):
            $p[0] - productId
            $p[1] - quantity
            $p[2] - variationId
        */
        try {
            $cartData = self::cartDeserialize($rawData);
        } catch (\Exception $e) {
            return;
        }

        try {
            global $woocommerce;
            $oldCart = $woocommerce->cart->get_cart();
            $oldCartIds = array();
            foreach ($oldCart as $product) {
                $oldCartIds[] = $product['data']->get_id();
            }

            //Setup WooCommerce (create session if it does not exist yet)
            Helper::wc()->frontend_includes();
            if(empty($oldCartIds)) {
	            Helper::wc()->session = new \WC_Session_Handler();
	            Helper::wc()->session->init();
	            Helper::wc()->customer = new \WC_Customer(Helper::getCurrentUserId(), true);
	            Helper::wc()->cart = new \WC_Cart();
            }

            if ($cartData) {
               foreach ($cartData as $product) {
                    $productFields = explode(self::DELIMITER, $product);
                    if(!isset($productFields[0])
                        || !$productFields[0]
                        || in_array($productFields[0], $oldCartIds)) {
                        continue;
                    }
                    $productId   = (int)$productFields[0];
                    $quantity    = isset($productFields[1]) ? (int)$productFields[1] : 1;
                    $variationId = isset($productFields[2]) ? (int)$productFields[2] : 0;
					Helper::wc()->cart->add_to_cart($productId, $quantity, $variationId, array(), ['cart_recovery' => true]);
                }

                //Allow other hooks for totals calculation:
	            Helper::wc()->cart->calculate_totals();
            }
        } catch (\Exception $e) {
            error_log(print_r($e->getMessage(), true));
        }
    }

    public function getCartRecoveryUrl()
    {
        global $woocommerce;

        $cart = $woocommerce->cart->get_cart();

        $recoveryCartObj  = array();
        foreach ($cart as $product) {
            $id = $product['data']->get_id();
            $WcProduct = Helper::wcGetProduct($id);
            $recoveryCartObj[] =
                $WcProduct->get_id() . self::DELIMITER .
                $product['quantity'] . self::DELIMITER .
                $product['variation_id'];
        }
        $recoveryCartUrl = Helper::getQueryArgs($woocommerce->query_string, '', Helper::getHomeUrl($woocommerce->request)) . "?rest_route=/salesmanago/v1/cart";
        $recoveryCartUrl .= "&c=" . self::cartSerialize($recoveryCartObj);
        if($smClient = WcContactModel::getSmClient()) {
            $recoveryCartUrl .= "&s=" . $smClient;
        }
        return $recoveryCartUrl;
    }

    public static function cartSerialize($data)
    {
        if(function_exists("gzdeflate") && function_exists("gzinflate")) {
            return urlencode(base64_encode(gzdeflate(json_encode($data))));
        }
        return urlencode(base64_encode(json_encode($data)));
    }

    public static function cartDeserialize($data)
    {
        if (function_exists("gzdeflate") && function_exists("gzinflate")) {
            return json_decode(gzinflate(base64_decode(urldecode($data))));
        }
        return json_decode(base64_decode(urldecode($data)));
    }
}
