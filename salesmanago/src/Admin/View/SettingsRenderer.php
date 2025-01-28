<?php

namespace bhr\Admin\View;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Controller\ProductCatalogController;
use bhr\Admin\Entity\MessageEntity;
use bhr\Admin\Model\AdminModel;
use bhr\Admin\Entity\Plugins\Cf7;
use bhr\Admin\Entity\Plugins\Gf;
use bhr\Admin\Entity\Plugins\Ff;
use bhr\Admin\Model\ProductCatalogModel;
use bhr\Frontend\Model\MonitCodeModel;

use Exception;

class SettingsRenderer {

	/**
	 * @var AdminModel
	 */
	private $AdminModel;

	/**
	 * @var ProductCatalogController
	 */
	protected $ProductCatalogController;

	/**
	 * @var bool
	 */
	protected $catalogsLimitReached = false;

	/**
	 * @var array
	 */
	private array $customDetails;

	/**
	 * @var array
	 */
	private array $systemDetails;

	/**
	 * @var array
	 */
	private array $detailsMapping;

	/**
	 * @var array
	 */
	private array $attributes;

	public function __construct( AdminModel $AdminModel ) {
		$this->AdminModel = $AdminModel;
		$ProductCatalogModel = new ProductCatalogModel( $this->AdminModel );
		$this->ProductCatalogController = new ProductCatalogController( $ProductCatalogModel );
	}

	/**
	 *
	 */
	public function getSettingsView() {
		try {
			$page       = $this->AdminModel->getPage();
			$userLogged = $this->AdminModel->getUserLogged();
		} catch ( Exception $e ) {
			MessageEntity::getInstance()
				->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 602 : $e->getCode() ) );
		}
		echo( '<div class="wrap" id="salesmanago">' );
		echo( '
        <a href="https://salesmanago.com/login.htm?&utm_source=integration&utm_medium=wordpress&utm_content=logo" target="_blank">
            <img id="salesmanago-logo" src="' . $this->AdminModel->getPluginUrl() . 'src/Admin/View/img/logo.svg" alt="SALESmanago"/>
        </a>' );
		echo( MessageEntity::getInstance()->getMessagesHtml() );
		if ( empty( $page ) ) {
			echo( '</div>' );
			return;
		}
		try {
			/* User logged */
			if ( $userLogged ) {
				include __DIR__ . '/partials/navbar.php';
				switch ( $page ) {
					case 'salesmanago':
						try {
							include __DIR__ . '/integration_settings.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 610 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-login':
						try {
							include __DIR__ . '/login_form.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 110 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-monit-code':
						try {
							$context = 'monitcode';
							include __DIR__ . '/monitcode.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 690 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-export':
						try {
							$installedDate = $this->AdminModel->getPluginInstalledDate();
							include __DIR__ . '/export.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 620 : $e->getCode() ) );
						}
						break;
                    case 'salesmanago-product-catalog':
                        try {
							if ( isset( $_REQUEST['subpage'] ) && $_REQUEST['subpage'] === 'create-catalog' ) {
								$catalogsLimit = $this->ProductCatalogController->getCatalogsLimit();
								$catalogsAmount = count($this->ProductCatalogController->getCatalogList());

								if ( $catalogsAmount >= $catalogsLimit ) {
									$this->catalogsLimitReached = true;
								}

								include __DIR__ . '/product_api/create_catalog.php';
							} else {
								if ( isset ( $_POST [ 'attribute_mapping' ] ) ) {
									$this->ProductCatalogController
										->sanitizeMapping( $_POST [ 'attribute_mapping' ] )
										->setDetailsMappingToPlatformSettings();
								}
								$this->customDetails = $this->ProductCatalogController->getCustomDetails();
								$this->systemDetails = $this->ProductCatalogController->getSystemDetails();
								$this->attributes = $this->ProductCatalogController->getAttributesNames();
								$this->detailsMapping = $this->AdminModel->getPlatformSettings()->getDetailsMapping();

								include __DIR__ . '/product_api/product_catalog.php';
							}
                        } catch ( Exception $e ) {
                            MessageEntity::getInstance()
                                 ->setMessagesAfterView( true )
                                 ->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 620 : $e->getCode() ) );
                        }
                        break;
					case 'salesmanago-plugins':
						try {
							include __DIR__ . '/plugins.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 630 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-plugin-wp':
						try {
							$context = SUPPORTED_PLUGINS['WordPress'];
							include __DIR__ . '/plugins/plugin_wp.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 640 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-plugin-wc':
						try {
							$context = SUPPORTED_PLUGINS['WooCommerce'];
							if ( ! $this->AdminModel->getInstalledPluginByName( $context ) ) {
								echo( '<div class="salesmanago-notice notice notice-warning">' . __( 'This plugin was not detected.', 'salesmanago' ) . '</div>' );
							}
							include __DIR__ . '/plugins/plugin_wc.php';
						} catch ( Exception $e ) {
								MessageEntity::getInstance()
									->setMessagesAfterView( true )
									->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 650 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-plugin-cf7':
						try {
							$context = SUPPORTED_PLUGINS['Contact Form 7'];
							if ( ! $this->AdminModel->getInstalledPluginByName( $context ) ) {
								echo( '<div class="salesmanago-notice notice notice-warning">' . __( 'This plugin was not detected.', 'salesmanago' ) . '</div>' );
							}
							$availableFormsList = Cf7::listAvailableForms();
							include __DIR__ . '/plugins/plugin_cf7.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 660 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-plugin-gf':
						try {
							$context = SUPPORTED_PLUGINS['Gravity Forms'];
							if ( ! $this->AdminModel->getInstalledPluginByName( $context ) ) {
								echo( '<div class="salesmanago-notice notice notice-warning">' . __( 'This plugin was not detected.', 'salesmanago' ) . '</div>' );
							} else {
								$availableFormsList = Gf::listAvailableForms();
							}
							include __DIR__ . '/plugins/plugin_gf.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 670 : $e->getCode() ) );
						}
						break;
					case 'salesmanago-plugin-ff':
						try {
							$context = SUPPORTED_PLUGINS['Fluent Forms'];
							if ( ! $this->AdminModel->getInstalledPluginByName( $context ) ) {
								echo( '<div class="salesmanago-notice notice notice-warning">' . __( 'This plugin was not detected.', 'salesmanago' ) . '</div>' );
							} else {
								$availableFormsList = Ff::listAvailableForms();
							}
							include __DIR__ . '/plugins/plugin_ff.php';
						} catch ( Exception $e ) {
							MessageEntity::getInstance()
								->setMessagesAfterView( true )
								->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 680 : $e->getCode() ) );
						}
						break;
                    case 'salesmanago-about':
                        try {
                            $data = $this->AdminModel->getAboutInfo();
                            $logs = $this->AdminModel->getErrorLog();
							$api_v3_logs = $this->AdminModel->getErrorLog( true );
							$is_new_api_v3_error = $this->AdminModel->getConfiguration()->isNewApiError();
                            include __DIR__ . '/about.php';
                        } catch ( Exception $e ) {
                            MessageEntity::getInstance()
                                 ->setMessagesAfterView( true )
                                 ->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 680 : $e->getCode() ) );
                        }
                        break;
                    default:
						include __DIR__ . '/integration_settings.php';
						break;
				}

				/* User not logged */
			} else {
				if ( $page == 'salesmanago' ) {
					include __DIR__ . '/login_form.php';
					return;
				}
			}

			/* Always available */
			if ( $page == 'salesmanago-go-to-app' ) {
				include __DIR__ . '/go_to_app.php';
				return;
			}
		} catch ( Exception $e ) {
			MessageEntity::getInstance()
				->setMessagesAfterView( true )
				->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 602 : $e->getCode() ) );
		}
		if ( MessageEntity::getInstance()->isMessagesAfterView() ) {
			echo( MessageEntity::getInstance()->getMessagesHtml() );
		}
		// closing of main wrap #salesmanago
		echo( '</div>' );
	}

	/**
	 * @param $value
	 * @param $name
	 * @param string $context
	 * @return bool|string
	 */
	public function selected( $value, $name, $context = '' ) {
		try {
			switch ( $name ) {
				case 'cron':
					return ( $this->AdminModel->getPlatformSettings()->getCronEnabled() ? 'checked' : '' );
				case 'contact-cookie-ttl-default':
					$contactCookieTtl = $this->AdminModel
											->getConfiguration()
											->getContactCookieTtl();
					return ( $this->AdminModel->isDefaultContactCookieLifetime());
					break;
				case 'event-cookie-ttl':
					return ( $this->AdminModel
							->getConfiguration()
							->getEventCookieTtl() == $value )
								? 'selected'
								: '';
					break;
				case 'language-detection':
					if ( ! empty(
						$this->AdminModel
						->getPlatformSettings()
						->getLanguageDetection()
					)
					) {
						echo ( $this->AdminModel
								->getPlatformSettings()
								->getLanguageDetection() == $value )
									? 'selected'
									: '';
					}
					break;
				case 'salesmanago-monitcode-disable-monitoring-code':
					if ( $this->AdminModel
							->getPlatformSettings()
							->getMonitCode()
							->isDisableMonitoringCode() === $value ) {
						return 'checked';
					}
					break;
				case 'salesmanago-monitcode-smcustom':
					echo ( $this->AdminModel
							->getPlatformSettings()
							->getMonitCode()
							->isSmCustom() === $value )
								? 'checked'
								: '';
					break;
				case 'salesmanago-monitcode-smbanners':
					echo ( $this->AdminModel
							->getPlatformSettings()
							->getMonitCode()
							->isSmBanners() === $value )
								? 'checked'
								: '';
					break;
				case 'salesmanago-monitcode-popup-js':
					echo ( $this->AdminModel
							->getPlatformSettings()
							->getMonitCode()
							->isPopupJs() === $value )
								? 'checked'
								: '';
					break;
				case 'plugins':
					echo ( $this->AdminModel
						->getPlatformSettings()
						->isActive( $value ) )
							? 'checked'
							: '';
					break;
				case 'double-opt-in-active':
					if ( $this->AdminModel
						->getPlatformSettings()
						->getPluginByName( $context )
						->getDoubleOptIn()
						->isActive() ) {
						return 'checked';
					}
					return '';
				case 'owner':
					if ( ! empty(
						$this->AdminModel
								->getPlatformSettings()
								->getPluginByName( $context )
								->getOwner()
					)
					) {
						return ( $this->AdminModel
								->getPlatformSettings()
								->getPluginByName( $context )
								->getOwner() == $value )
									? 'selected'
									: '';
					}
					break;
				case 'opt-in-input-active':
					return ! ( $this->AdminModel
							->getPlatformSettings()
							->getPluginByName( $context )
							->getOptInInput()
							->getMode() == 'none' );
				case 'opt-in-input-mode':
					$mode = $this->AdminModel
							->getPlatformSettings()
							->getPluginByName( $context )
							->getOptInInput()
							->getMode();
					if ( empty( $value ) ) {
						return $mode;
					} else {
						echo ( $value === $mode )
								? 'selected'
								: '';
					}
					break;
				case 'opt-in-mobile-input-active':
					return ! ( $this->AdminModel
								 ->getPlatformSettings()
								 ->getPluginByName( $context )
								 ->getOptInMobileInput()
								 ->getMode() == 'none' );
				case 'opt-in-mobile-input-mode':
					$mode = $this->AdminModel
						->getPlatformSettings()
						->getPluginByName( $context )
						->getOptInMobileInput()
						->getMode();
					if ( empty( $value ) ) {
						return $mode;
					} else {
						echo ( $value === $mode )
							? 'selected'
							: '';
					}
					break;
				case 'product-identifier-type':
					if ( ! empty( $context ) ) {
						$extEventId = $this->AdminModel
							->getPlatformSettings()
							->getPluginByName( $context )
							->getProductIdentifierType();
						return ( $value === $extEventId )
									? 'selected'
									: '';
					}
					break;
				case 'purchase-hook':
					$purchaseHook = $this->AdminModel
						->getPlatformSettings()
						->getPluginByName( $context )
						->getPurchaseHook();
					return ( $value === $purchaseHook )
								? 'selected'
								: '';
					break;
				case 'prevent-event-duplication':
					echo ( $this->AdminModel
						->getPlatformSettings()
						->getPluginByName( $context )
						->isPreventEventDuplication() )
							? 'checked'
							: '';
					break;
				case 'properties-type':
					return ( $this->AdminModel
						->getPlatformSettings()
						->getPluginByName( $context )
						->getPropertiesMappingMode() === $value )
							? 'selected'
							: '';

			}
		} catch ( Exception $e ) {
				MessageEntity::getInstance()
					->setMessagesAfterView( true )
					->addException( new Exception( $e->getMessage(), $e->getCode() == 0 ? 602 : $e->getCode() ) );
		}
		return '';
	}

	/**
	 * @param $context
	 * @return string|void
	 */
	public function getNoFormsMessageByPluginName( $context ) {
		return method_exists( $this->AdminModel->getPlatformSettings()->getPluginByName( $context ), 'getNoFormsMessage' )
			? $this->AdminModel->getPlatformSettings()->getPluginByName( $context )->getNoFormsMessage()
			: __( 'No forms found', 'salesmanago' );
	}

	/**
	 * @param $tab
	 * @return string
	 */
	public function active( $tab ) {
		return ( $this->AdminModel->getPage() == $tab ) ? 'nav-tab-active' : '';
	}

	/**
	 * @param $tab
	 * @return bool
	 */
	public function available( $tab ) {
		return ( $this->AdminModel->isTabAvailable( $tab ) );
	}

	public function showMonitCode() {
		return MonitCodeModel::getMonitCode(
			$this->AdminModel->getConfiguration()->getClientId(),
			$this->AdminModel->getConfiguration()->getEndpoint(),
			$this->AdminModel->getConfiguration()->getSmApp(),
			array(
				'disabled'  => $this->AdminModel->getPlatformSettings()->getMonitCode()->isDisableMonitoringCode(),
				'smcustom'  => $this->AdminModel->getPlatformSettings()->getMonitCode()->isSmCustom(),
				'smbanners' => $this->AdminModel->getPlatformSettings()->getMonitCode()->isSmBanners(),
				'popUpJs'   => $this->AdminModel->getPlatformSettings()->getMonitCode()->isPopUpJs(),
			),
			'admin'
		);
	}
}
