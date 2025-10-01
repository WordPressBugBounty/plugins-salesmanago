<?php
namespace bhr\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Entity\MessageEntity;
use bhr\Admin\Entity\PlatformSettings;
use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\Helper;
use bhr\Admin\Model\ProductCatalogModel;
use bhr\Admin\View\SettingsRenderer;
use bhr\Admin\Controller\LoginController;
use bhr\Admin\Controller\ReportingController;

use Error;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\UserAccountService;
use bhr\Includes\SecureHelper;

class SettingsController
{
	public $SettingsRenderer;
	private $AdminModel;
	protected $UserModel;

	public function __construct( AdminModel $AdminModel ) {
		$this->AdminModel = $AdminModel;

		if ( isset( $_REQUEST['page'] ) ) {
			$this->AdminModel->setPage( $_REQUEST['page'] );
		}

	}

	/**
	 * Include settings view
	 */
	public function includeSettingsView() {
		$this->AdminModel->setInstalledPlugins();
		$this->SettingsRenderer = new SettingsRenderer( $this->AdminModel );
		$this->SettingsRenderer->getSettingsView();
	}

	/**
	 *
	 */
	public function registerMenuPages() {
        Helper::addMenuPage(
            __( 'General settings of SALESmanago&Leadoo integration', 'salesmanago' ),
            __( 'SALESmanago & Leadoo', 'salesmanago' ),
            SALESMANAGO_AND_LEADOO,
            SALESMANAGO_AND_LEADOO,
            array( $this, 'includeSettingsView' ),
            plugins_url( '../View/img/icon2.svg', __FILE__ ),
            55
        );

        Helper::addSubmenuPage(
            SALESMANAGO_AND_LEADOO,
            __( 'General settings of SALESmanago&Leadoo integration', 'salesmanago' ),
            __( 'Integrations', 'salesmanago' ),
            SALESMANAGO,
            SALESMANAGO_AND_LEADOO . '-main',
            array( $this, 'includeSettingsView' )
        );

		// First submenu position (this will open as default)
        // If user logged - show Integration Settings
        Helper::addSubmenuPage(
            $this->AdminModel->getUserLogged() ? SALESMANAGO_AND_LEADOO : null,
            __( 'Manage SM integration settings', 'salesmanago' ),
            __( 'Integration settings', 'salesmanago' ),
            SALESMANAGO,
            SALESMANAGO . '-integration-settings',
            array( $this, 'includeSettingsView' )
        );

        // Otherwise - show login screen
        Helper::addSubmenuPage(
            !$this->AdminModel->getUserLogged() ? SALESMANAGO_AND_LEADOO : null,
            __( 'Login to SALESmanago account', 'salesmanago' ),
            __( 'SALESmanago', 'salesmanago' ),
            SALESMANAGO,
            SALESMANAGO . '-login',
            array( $this, 'includeSettingsView' ),
        );

		// Other tabs
		// monitcode
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-monit-code' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Monitoring code features', 'salesmanago' ),
				__( 'Monitoring code', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-monit-code',
				array( $this, 'includeSettingsView' )
			);
		}
		// Other tabs
		// Export
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-export' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Export contacts and events', 'salesmanago' ),
				__( 'Export', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-export',
				array( $this, 'includeSettingsView' )
			);
		}

        //Product catalog
        if ( $this->AdminModel->isTabAvailable( 'salesmanago-product-catalog' ) ) {
            Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
                __( 'Manage product catalog', 'salesmanago' ),
                __( 'Product catalog', 'salesmanago' ),
                SALESMANAGO,
                SALESMANAGO . '-product-catalog',
                array( $this, 'includeSettingsView' )
            );
        }

		// Plugins
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-plugins' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Manage integrations with plugins', 'salesmanago' ),
				__( 'Plugins', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-plugins',
				array( $this, 'includeSettingsView' )
			);
		}
		// Plugins - WordPress
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-plugin-wp' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Manage WordPress integration', 'salesmanago' ),
				__( 'WordPress', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-plugin-wp',
				array( $this, 'includeSettingsView' )
			);
		}
		// Plugins - WooCommerce
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-plugin-wc' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Manage WooCommerce integration', 'salesmanago' ),
				__( 'WooCommerce', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-plugin-wc',
				array( $this, 'includeSettingsView' )
			);
		}
		// Plugins - Contact Form 7
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-plugin-cf7' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Manage Contact Form 7 integration', 'salesmanago' ),
				__( 'Contact Form 7', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-plugin-cf7',
				array( $this, 'includeSettingsView' )
			);
		}
		// Plugins - Gravity Forms
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-plugin-gf' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Manage Gravity Forms integration', 'salesmanago' ),
				__( 'Gravity Forms', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-plugin-gf',
				array( $this, 'includeSettingsView' )
			);
		}
		// Plugins - Fluent Forms
		if ( $this->AdminModel->isTabAvailable( 'salesmanago-plugin-ff' ) ) {
			Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
				__( 'Manage Fluent Forms integration', 'salesmanago' ),
				__( 'Fluent Forms', 'salesmanago' ),
                SALESMANAGO,
				SALESMANAGO . '-plugin-ff',
				array( $this, 'includeSettingsView' )
			);
		}

        if ($this->AdminModel->isTabAvailable("salesmanago-about")) {
            Helper::addSubmenuPage(
                SALESMANAGO_AND_LEADOO,
                __('Get system information', 'salesmanago'),
                __('About', 'salesmanago'),
                SALESMANAGO,
                SALESMANAGO . '-about',
                array($this, 'includeSettingsView')
            );
        }

        Helper::addSubmenuPage(
        //if not logged in salesmanago integration:
            (isset($_GET['page'])
                && $this->AdminModel->getUserLogged()
                && preg_match('/^salesmanago-(?!and-leadoo-main$).+/', $_GET['page'])
            )
                ? SALESMANAGO_AND_LEADOO
                : null,
            __( 'Discover Leadoo', 'salesmanago' ),
            __( 'Discover Leadoo', 'salesmanago' ),
            SALESMANAGO,
            SALESMANAGO . '-discover-leadoo',
            array( $this, 'includeSettingsView' )
        );

        Helper::addSubmenuPage(
            SALESMANAGO_AND_LEADOO,
            __( 'General settings of Leadoo integration', 'salesmanago' ),
            __( 'Leadoo', 'salesmanago' ),
            SALESMANAGO,
            LEADOO,
            array( $this, 'includeSettingsView' )
        );

        //Added to select Discover SALESmanago submenu:
        add_filter( 'submenu_file', function( $submenu_file ) {
            // only on our plugin's top‐level
            if ( isset( $_GET['page'] ) && $_GET['page'] === 'leadoo' ) {
                // if action=discover, highlight that submenu slug
                if ( isset( $_GET['action'] ) && $_GET['action'] === 'discover' ) {
                    return 'leadoo&action=discover';
                }
                // otherwise fall back to your “main” submenu slug
                return 'leadoo';
            }
            return $submenu_file;
        });

        Helper::addSubmenuPage(
            //if not logged in salesmanago integration:
            isset($_GET['page']) && (
                ($_GET['page'] === 'leadoo')
                || $_GET['page'] === 'salesmanago-discover-salesmanago'
            ) ? SALESMANAGO_AND_LEADOO
                : null,
            __( 'Discover SALESmanago', 'salesmanago' ),
            __( 'Discover SALESmanago', 'salesmanago' ),
            SALESMANAGO,
            LEADOO . '&action=discover',
            array( $this, 'includeSettingsView' )
        );

		Helper::addSubmenuPage(
            SALESMANAGO_AND_LEADOO,
			__( 'Manage integrations with plugins', 'salesmanago' ),
			__( 'salesmanago.com', 'salesmanago' ),
            SALESMANAGO,
			'https://salesmanago.com/'
		);

        Helper::addSubmenuPage(
            SALESMANAGO_AND_LEADOO,
            __( 'Manage integrations with plugins', 'salesmanago' ),
            __( 'leadoo.com', 'salesmanago' ),
            SALESMANAGO,
            'https://leadoo.com/'
        );
	}

	/**
	 *
	 */
	public function setUserLogged() {
		try {
			if ( ! empty( $this->AdminModel->getConfiguration()->isActive() )
			&& ! empty( $this->AdminModel->getConfiguration()->getToken() ) ) {
				$this->AdminModel->setUserLogged( true );
			}
		} catch ( \Exception $e ) {
			MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 500 ) );
		}
	}

    /**
     * @return void
     */
	public function route() {
        if ( ! empty( $_REQUEST['action'] ) ) {
            if ( ! SecureHelper::validate_nonce( $_REQUEST['sm_nonce'] ?? '', $_REQUEST['action'] )) {
                return;
            }

			switch ( $_REQUEST['action'] ) {
				case 'login':
					$LoginController = new LoginController( $this->AdminModel );
					$LoginController->loginUser( $_REQUEST );
					break;
				case 'logout':
					$LoginController = new LoginController( $this->AdminModel );
					$LoginController->logoutUser();
					break;
				case 'refreshOwnerList':
					$this->refreshOwnerList();
					break;
				case 'refreshCatalogList':
					$this->refreshCatalogs();
					break;
                case 'save':
					$this->AdminModel->parseSettingsFromRequest( $_REQUEST );
					$this->AdminModel->saveConfiguration();
					$this->AdminModel->savePlatformSettings();
					MessageEntity::getInstance()->addMessage( 'Settings have been saved.', 'success', 703 );
                    break;
                case 'addApiV3Key':
                    $ProductCatalogModel = new ProductCatalogModel( $this->AdminModel );
                    $ProductCatalogController = new ProductCatalogController( $ProductCatalogModel );
                    $ProductCatalogController->processApiV3Key( $_REQUEST );
                    break;
                case 'addProductCatalog':
                    $ProductCatalogModel = new ProductCatalogModel( $this->AdminModel );
                    $ProductCatalogController = new ProductCatalogController( $ProductCatalogModel );
					$ProductCatalogController->processCatalogCreateRequest();
                    break;
                case 'setActiveCatalog':
	                $ProductCatalogModel = new ProductCatalogModel( $this->AdminModel );
	                $ProductCatalogController = new ProductCatalogController( $ProductCatalogModel );
	                $ProductCatalogController->processSetActiveCatalogRequest( $_REQUEST );
					break;
				case 'acknowledgeProductApiError':
					$CallbackController = new CallbackController();
					$CallbackController->acknowledge_callback_message();
					break;
            }
		}
		if ( ! empty( $_REQUEST['message'] ) ) {
			switch ( $_REQUEST['message'] ) {
				case 'logout':
					MessageEntity::getInstance()->addMessage( 'Logged out.', 'success', 702 );
					break;
				case 'logout-error':
						MessageEntity::getInstance()->addException( new Exception( __( 'Error on logout' ), 151 ) );
					break;
			}
		}
	}

    /**
     * @return void
     */
	public function setAvailableTabs() {
		if ( $this->AdminModel->getUserLogged() ) {
			$this->AdminModel->setAvailableTabs( array( SALESMANAGO, SALESMANAGO . '-monit-code', SALESMANAGO . '-export', SALESMANAGO . '-plugins', SALESMANAGO . '-product-catalog', SALESMANAGO . '-about' ) );

			foreach ( SUPPORTED_PLUGINS as $key => $value ) {
				if ( $this->AdminModel->getPlatformSettings()->isActive( $value ) ) {
					$this->AdminModel->appendAvailableTabs( SALESMANAGO . '-plugin-' . $value );
				}
			}
		} else {
			$this->AdminModel->setAvailableTabs( array( SALESMANAGO ) );
		}

	}

    /**
     * @return array
     * @throws Exception
     */
	public function refreshOwnerList() {
		$UserAccountService = new UserAccountService( $this->AdminModel->Configuration );
		try {
			$Response = $UserAccountService->getOwnersList();
			if ( ! $Response->getStatus() ) {
				MessageEntity::getInstance()->addMessage( 'False response while getting owner list', 120 );
			}
			$owners = $Response->getField( 'users' );
			if ( ! empty( $owners ) && is_array( $owners ) ) {
				$this->AdminModel->Configuration->setOwnersList( $owners );
				$this->AdminModel->saveConfiguration();
				return $owners;
			}
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 120 ) );
		}
	}

	/**
	 * Refresh Product Catalog list
	 *
	 * @return array|void
	 */
	public function refreshCatalogs(){
		try {
			$ProductCatalogModel = new ProductCatalogModel( $this->AdminModel );
			$ProductCatalogController = new ProductCatalogController( $ProductCatalogModel );
			return $ProductCatalogController->getCatalogList();
		} catch ( Error | \Exception $ex ) {
			Helper::salesmanago_log( $ex->getMessage(), __FILE__ );
		}
	}

	/**
	 * @return void
	 */
	public function checkPluginVersion() {
		try {
			$currentPluginVersion   = SM_VERSION;
			$lastSavedPluginVersion = $this->AdminModel->getPlatformSettings()->getPluginVersion();

			if ( ! $lastSavedPluginVersion ) {
				$lastSavedPluginVersion = '0.0.0';
			}

			if ( version_compare( $lastSavedPluginVersion, $currentPluginVersion, '!=' ) ) {
				$ReportingController = new ReportingController( $this->AdminModel );
				$ReportingController->reportUserAction( ReportingController::ACTION_PLUGIN_UPDATE, $lastSavedPluginVersion, $currentPluginVersion );

				$this->AdminModel->getPlatformSettings()->setPluginVersion( SM_VERSION );
				$this->AdminModel->savePlatformSettings();
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}
	}
}
