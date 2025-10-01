<?php

namespace bhr\Includes;

use bhr\Admin\Entity\MessageEntity;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SecureHelper
{
    protected static $instance = [
        'login',
        'logout',
        'refreshOwnerList',
        'refreshCatalogList',
        'save',
        'addApiV3Key',
        'addProductCatalog',
        'setActiveCatalog',
        'acknowledgeProductApiError',


        'salesmanago_refresh_catalogs',
        'salesmanago_settings_save'
    ];

    /**
     * Validates nonce for given action
     *
     * @param string $nonce
     * @param string $action
     * @return bool
     */
    public static function validate_nonce($nonce, $action) {

        if ( function_exists( 'current_user_can' ) && ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        $actions_requiring_nonce = array(
            'login',
            'logout',
            'refreshOwnerList',
            'refreshCatalogList',
            'save',
            'addApiV3Key',
            'addProductCatalog',
            'setActiveCatalog',
            'acknowledgeProductApiError',

            'salesmanago_refresh_catalogs',
            'salesmanago_settings_save',

            'salesmanago_generate_swjs',

        );

        if ( in_array( $_REQUEST['action'], $actions_requiring_nonce, true )
            && function_exists( 'wp_verify_nonce' )
        ) {
            $action = $_REQUEST['action'];

            $nonce = $_REQUEST['sm_nonce'] ?? ($_REQUEST['nonce'] ?? '');

            if ( ! $nonce || ! wp_verify_nonce( $nonce, $action ) ) {
                MessageEntity::getInstance()->addMessage( 'Not authorized. Please refresh the view', 'error', 403);
                return false;
            }
        }

        return true;
    }

    /**
     * Validates nonce for ajax requests
     *
     * @param string $action
     */
    public static function validate_ajax_nonce($action) {
        if ( function_exists( 'current_user_can' ) && ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        $actions_requiring_nonce = [
            'salesmanago_export_count_contacts',
            'salesmanago_export_contacts',
            'salesmanago_export_count_events',
            'salesmanago_export_events',
            'salesmanago_export_products'
        ];

        if (! in_array($action, $actions_requiring_nonce, true)) {
            return false;
        }

        if ( function_exists( 'check_ajax_referer' ) ) {
            check_ajax_referer( 'salesmanago_admin', 'sm_nonce' );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
        }
    }
}