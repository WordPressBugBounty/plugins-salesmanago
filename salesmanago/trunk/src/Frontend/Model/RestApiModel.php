<?php

namespace bhr\Frontend\Model;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\Settings as SettingsModel;
use bhr\Frontend\Plugins\Wc\WcCartRecoveryModel;

class RestApiModel
{
    private $SettingsModel;

    public function __construct(SettingsModel $SettingsModel)
    {
        $this->SettingsModel = $SettingsModel;

        Helper::addAction('rest_api_init', function() {
            Helper::registerRestRoute( 'salesmanago/v1', '/cart', array(
                'methods' => 'GET',
                'callback' => __CLASS__ . '::cartRecovery',
                'permission_callback' => function() {
                    return true;
                }
            ));
        } );
    }

    public static function cartRecovery()
    {
        if(isset($_GET['test'])) {
            self::testRestApi();
        }
        if(isset($_GET['c'])) {
            WcCartRecoveryModel::recoverCart($_GET['c']);
        }
        if(isset($_GET['s'])) {
            $CookieManager = new CookieManager();
            $CookieManager->setCookie(AbstractModel::SM_CLIENT, $_GET['s']);
        }
        //Redirect user to a cart URL:
	    Helper::redirectToCart();
        exit();
    }

    private static function testRestApi()
    {
        //URl: if we got here it's OK
        echo('<table><tr><td>URL</td><td>'.__('OK', 'salesmanago')."</td></tr>");

        //REST access: pose as a guest user and check if rest_authentication_errors returns errors
        $user_id = Helper::setFilter('determine_current_user', false);
        Helper::setCurrentUser(0); //guest
        $result = Helper::setFilter('rest_authentication_errors', null);
        Helper::setCurrentUser($user_id); //back to current user
        if(!is_wp_error($result)) {
            echo('<tr><td>REST access</td><td>') . __('OK', 'salesmanago') . '</td></tr>';
        } else {
            echo('<tr><td>REST access</td><td>') . __('Problem', 'salesmanago') . '</td></tr>';
        }

        //Test functions used by cart recovery (gzdeflate is most critical)
        $functionsToTest = array(
            'gzdeflate',
            'gzinflate',
            'json_encode',
            'wp_redirect',
            'wc',
            'wc_get_cart_url'
        );
        foreach ($functionsToTest as $function) {
            if (function_exists($function)) {
                echo("<tr><td>$function</td><td>" . __('OK', 'salesmanago') . "</td></tr>");
            } else {
                echo("<tr><td>$function</td><td>" . __('Problem', 'salesmanago') . "</td></tr>");
            }
        }

        //Check if global $woocommerce is available
        global $woocommerce;
        if (isset($woocommerce)) {
            echo '<tr><td>global $woocommerce</td><td>' . __('OK', 'salesmanago') . '</td></tr></table>';
        } else {
            echo '<tr><td>global $woocommerce</td><td>' . __('Problem', 'salesmanago') . '</td></tr></table>';
        }

        exit();
    }
}
