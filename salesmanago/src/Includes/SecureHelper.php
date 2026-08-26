<?php

namespace bhr\Includes;

use bhr\Admin\Entity\MessageEntity;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SecureHelper
{
    private const AJAX_ACTIONS = array(
        'salesmanago_export_count_contacts',
        'salesmanago_export_contacts',
        'salesmanago_export_count_events',
        'salesmanago_export_events',
        'salesmanago_export_products'
    );

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
			'setActiveCatalogs',
            'acknowledgeProductApiError',
            'salesmanago_refresh_catalogs',
            'salesmanago_settings_save',
            'salesmanago_generate_swjs',
        );

        $request_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';

        $action_to_verify = ! empty( $action ) ? $action : $request_action;

        if ( in_array( $request_action, $actions_requiring_nonce, true )
            && function_exists( 'wp_verify_nonce' )
        ) {
            $nonce_from_request = isset( $_REQUEST['sm_nonce'] )
                ? sanitize_text_field( $_REQUEST['sm_nonce'] )
                : ( isset( $_REQUEST['nonce'] ) ? sanitize_text_field( $_REQUEST['nonce'] ) : '' );

            if ( ! $nonce_from_request || ! wp_verify_nonce( $nonce_from_request, $action_to_verify ) ) {
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
     * @return void Exit when validation fails
     */
    public static function validate_ajax_nonce($action) {
        $request_action = sanitize_text_field( $_REQUEST['action'] ?? '' );

        if (
            $request_action !== $action
            || ! in_array($request_action, self::AJAX_ACTIONS, true)
            || ! check_ajax_referer( 'salesmanago_admin', 'sm_nonce', false )
            || ! current_user_can( 'manage_options' )
        ) {
            wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
        }
    }
}
