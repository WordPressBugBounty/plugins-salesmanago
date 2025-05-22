<?php

namespace bhr\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Controller\AdminActionController;
use bhr\Admin\Controller\ProductCatalogController;
use bhr\Admin\Controller\SettingsController;
use bhr\Admin\Entity\MessageEntity;
use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\Helper;
use bhr\Admin\Model\ProductCatalogModel;
use bhr\Includes\Helper as IncludesHelper;
use bhr\Includes\GlobalConstant;
use Exception;
use SALESmanago\Exception\Exception as SmException;

class Admin {

	protected $SettingsController;
	protected $AdminModel;
	protected $AdminActionController;
	protected $ProductCatalogController;

	/**
	 * Setup plugin for admin panel.
	 */
	public function __construct() {
		$is_salesmanago_view = ( isset( $_REQUEST['page'] ) && strpos( sanitize_key( $_REQUEST['page'] ), SALESMANAGO ) !== false );

		try {
			Helper::loadPluginTextDomain( 'salesmanago', false, 'salesmanago/languages' );
			Helper::addAction( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( new SmException( $e->getMessage(), 603 ) );
		}

		try {
			$this->AdminModel               = new AdminModel();
			$this->SettingsController       = new SettingsController( $this->AdminModel );
			$ProductCatalogModel            = new ProductCatalogModel( $this->AdminModel );
			$this->ProductCatalogController = new ProductCatalogController( $ProductCatalogModel );
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( new SmException( $e->getMessage(), 600 ) );
		}

		$this->AdminModel->getConfigurationFromDb();
		$this->AdminModel->getPlatformSettingsFromDb();
		$this->AdminModel->insertCronTableIfNotExist();

		$this->AdminActionController = new AdminActionController(
			$this->AdminModel->getConfiguration(),
			$this->AdminModel->getPlatformSettings()
		);

		if ( $is_salesmanago_view ) {
			try {
				$this->SettingsController->route();
			} catch ( Exception $e ) {
				MessageEntity::getInstance()->addException( new SmException( $e->getMessage(), 604 ) );
			}
		}
		try {
			$this->SettingsController->setUserLogged();
			if ( $this->AdminModel->getUserLogged() && $is_salesmanago_view ) {
				$this->SettingsController->checkPluginVersion();
			}
			$this->SettingsController->setAvailableTabs();
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( new SmException( $e->getMessage(), 500 ) );
		}
		Helper::addAction( 'activated_plugin', array( $this, 'load_sm_plugin_last' ), 1 );
		Helper::addAction( 'deactivated_plugin', array( $this, 'load_sm_plugin_last' ), 1 );
		Helper::addAction( 'admin_menu', array( $this, 'register_admin_dashboard_page' ) );
		Helper::addAction( 'wp_ajax_salesmanago_refresh_owners', array( $this, 'refresh_owners' ) );
		Helper::addAction( 'wp_ajax_salesmanago_refresh_catalogs', array( $this, 'refresh_product_catalogs' ) );
		Helper::addAction( 'wp_ajax_salesmanago_generate_swjs', array( $this, 'generate_sw_js' ) );
		Helper::addAction( 'admin_enqueue_scripts', array( $this, 'enqueue_clarity_script' ), 11 );
		if ( $this->AdminModel->getPlatformSettings()->getPluginWc()->isActive() ) {
			Helper::addAction( 'woocommerce_order_status_cancelled', array( $this, 'wc_event_status_changed' ), 10, 1 );
			Helper::addAction( 'woocommerce_order_status_refunded', array( $this, 'wc_event_status_changed' ), 10, 1 );
			Helper::addAction( 'woocommerce_order_status_processing', array( $this, 'wc_event_status_changed' ), 10, 1 );
			Helper::addAction( 'woocommerce_new_product', array( $this, 'handle_wc_product' ), 10, 2 );
			Helper::addAction( 'woocommerce_update_product', array( $this, 'handle_wc_product' ), 10, 2 );
			Helper::addAction( 'woocommerce_before_delete_product_variation', array($this, 'delete_product_single_variation' ));
		}
		Helper::addAction( 'profile_update', array( $this, 'user_update' ), 10, 2 );
		Helper::addAction( 'user_register', array( $this, 'user_create' ), 10, 2 );
		Helper::addAction( 'admin_notices', array( $this, 'notify_about_api_v3_error' ), 10, 0 );

		Helper::addAction('wp_trash_post', array($this, 'delete_product'));
		Helper::addAction('wp_ajax_trash_post', array($this, 'delete_product'));
		Helper::addAction('untrash_post', array($this, 'restore_product'));
	}

    /**
     * Handle product deletion
     *
     * @return void
     */
	public function delete_product() {
		$this->handle_product_status_change('trash', true);
	}

    /**
     * Handle product deletion - a single variation case
     *
     * @return void
     */
    public function delete_product_single_variation( $post_id ) {
        if ( $this->AdminModel->getPlatformSettings()->getPluginWc()->isActive() ) {
            $product = wc_get_product( $post_id );
            $this->ProductCatalogController->upsertProduct( $product, true );
        };
    }

    /**
     * Handle product restoration
     *
     * @return void
     */
	public function restore_product() {
		$this->handle_product_status_change('untrash', false);
	}

    /**
     * @param $action
     * @param $is_deleted
     * @return void
     */
	private function handle_product_status_change($action, $is_deleted) {
		if (
			!$this->AdminModel->getPlatformSettings()->getPluginWc()->isActive()
			|| ( $_GET[ 'action' ] ?? '' ) !== $action
		) {
			return;
		}

		static $processed_parents = array();

		//bulk action
		if ( isset( $_GET[ 'post' ] ) && is_array( $_GET[ 'post' ] ) && ( $_GET[ 'post_type' ] ?? '' ) == 'product' ) {
			foreach ( $_GET[ 'post' ] as $post_id ) {
				$this->process_product( $post_id, $processed_parents, $is_deleted );
			}
		}
		//single action
		elseif ( isset( $_GET[ 'post' ] ) && !is_array( $_GET[ 'post' ] ) ) {
			$post_id = $_GET[ 'post' ];
			$this->process_product( $post_id, $processed_parents, $is_deleted );
		}
    }

	private function process_product($post_id, array &$processed_parents, $is_deleted) {
		$post = get_post( $post_id );
		if (
			!$post
			|| $post->post_type !== 'product'
			|| $post->post_parent !== 0
			|| in_array( $post_id, $processed_parents, true )
		) {
			return;
		}

		$processed_parents[] = $post_id;
		$product = wc_get_product( $post_id );
		$this->ProductCatalogController->upsertProduct( $product, $is_deleted );

	}

	/**
	 * Make sure that salesmanago plugin is loaded last so that woocommerce functions can be used
	 *
	 * @return void
	 */
	public function load_sm_plugin_last() {
		IncludesHelper::loadSMPluginLast();
	}

	/**
	 * Register salesmanago admin dashboard
	 *
	 * @return void
	 */
	public function register_admin_dashboard_page() {
		try {
			Helper::grantAccessToSalesmanagoPlugin( 'administrator' );
			$this->SettingsController->registerMenuPages();
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( new SmException( $e->getMessage(), 601 ) );
		}
	}

	/**
	 * Enqueue clarity script in salesmanago admin pages
	 *
	 * @param string $admin_page Hook suffix for the current admin page.
	 *
	 * @return void
	 */
	public function enqueue_clarity_script( $admin_page ) {
		if ( strpos( $admin_page, SALESMANAGO ) !== false ) {
			wp_enqueue_script(
				'salesmanago-clarity-script',
				plugin_dir_url( __FILE__ ) . 'View/js/salesmanago-clarity.js',
				array(),
				SM_VERSION,
				true
			);
		}
	}

	/**
	 * Check if there is a new api v3 error and display it
	 *
	 * @return void
	 */
	public function notify_about_api_v3_error() {
		$is_new_api_v3_error = $this->AdminModel->getConfiguration()->isNewApiError();
		if ( $is_new_api_v3_error ) {
			$api_v3_warning_notice =
				'<div id="sm-api-v3-warning-notice" class="salesmanago-notice notice notice-error">'
				. __( 'SALESmanago Product API Error detected. Check the About tab.', 'salesmanago' ) . '</div>';
			echo $api_v3_warning_notice;
		}
	}

	/**
	 * Register SM scripts and assets
	 *
	 * @return void
	 */
	public function register_assets() {
		try {
			Helper::wpEnqueueStyle(
				'salesmanago',
				plugin_dir_url( __FILE__ ) . 'View/css/salesmanago-admin.css',
				array(),
				SM_VERSION,
				'all'
			);
			Helper::wpEnqueueScript(
				'salesmanago',
				plugin_dir_url( __FILE__ ) . 'View/js/salesmanago-admin.js',
				array(),
				SM_VERSION,
				true
			);
			Helper::wpEnqueueScript(
				'salesmanago-filter-notifications',
				plugin_dir_url( __FILE__ ) . 'View/js/salesmanago-filter-notifications.js',
				array(),
				SM_VERSION,
				true
			);
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( new SmException( $e->getMessage(), 603 ) );
		}
	}

	/**
	 * Refresh the list of SM owners
	 *
	 * @return void
	 */
	public function refresh_owners() {
		echo $this->AdminModel->buildOptions( $this->SettingsController->refreshOwnerList() );
		wp_die();
	}

	/**
	 * Generate sw.js
	 *
	 * @return void
	 */
	public function generate_sw_js() {
		echo $this->AdminModel->generateSwJs();
		wp_die();
	}

	/**
	 * Refresh product catalog list
	 *
	 * @return void
	 */
	public function refresh_product_catalogs() {
		echo $this->AdminModel->return_catalogs_to_view( $this->SettingsController->refreshCatalogs() );
		wp_die();
	}

	/**
	 * Handle WC order status change.
	 *
	 * @param array $data Event data.
	 *
	 * @return void
	 */
	public function wc_event_status_changed( $data ) {
		$this->AdminActionController->orderStatusChanged( $data );
	}

	/**
	 * Handle Wc Product hooks
	 *
	 * @param int    $product_id Product id.
	 * @param object $wc_product WC product object.
	 *
	 * @return void
	 */
	public function handle_wc_product( $product_id, $wc_product ) {
		if ( $this->AdminModel->getPlatformSettings()->getCronEnabled() ) {
			$this->ProductCatalogController->storeProduct( $wc_product );
			return;
		}
		$this->ProductCatalogController->upsertProduct( $wc_product );
	}

	/**
	 * Update user
	 *
	 * @param int   $user_id User id.
	 * @param array $old_data Old user data.
	 *
	 * @return void
	 */
	public function user_update( $user_id, $old_data ) {
		if (
			! in_array( GlobalConstant::WP_USR_ROLE_SUBSCRIBER, $old_data->roles, true )
			&& ! in_array( GlobalConstant::WP_USR_ROLE_CUSTOMER, $old_data->roles, true )
		) {
			return;
		}
		$this->AdminActionController->updateUser( $user_id, $old_data );
	}

	/**
	 * Create user
	 *
	 * @param int   $user_id User id.
	 * @param array $user_data User data.
	 *
	 * @return void
	 */
	public function user_create( $user_id, $user_data = null ) {
		if (
			GlobalConstant::WP_USR_ROLE_SUBSCRIBER !== $user_data['role']
			&& GlobalConstant::WP_USR_ROLE_CUSTOMER !== $user_data['role']
		) {
			return;
		}
		$this->AdminActionController->updateUser( $user_id, $user_data );
	}
}
