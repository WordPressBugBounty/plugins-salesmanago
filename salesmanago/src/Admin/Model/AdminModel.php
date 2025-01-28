<?php

namespace bhr\Admin\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Controller\CronController;
use bhr\Admin\Controller\ReportingController;
use bhr\Admin\Entity\MessageEntity;
use bhr\Admin\Entity\PlatformSettings;

use bhr\Admin\Entity\Plugins\AbstractPlugin;
use bhr\Admin\Entity\Plugins\Wc;
use Error;
use SALESmanago\Entity\AbstractEntity;
use bhr\Admin\Entity\Configuration;
use SALESmanago\Exception\Exception;
use stdClass;

class AdminModel extends AbstractModel
{

	const platformName = 'WORDPRESS';

	public $userLogged = false;
	public $pluginDir;
	public $pluginUrl;
	public $page                = 'salesmanago';
	public $availableTabs       = array();
	protected $installedPlugins = array();

	public $Configuration;
	public $PlatformSettings;
	private $CronController;

	public function __construct() {
		parent::__construct();

		foreach ( SUPPORTED_PLUGINS as $key => $value ) {
			$this->installedPlugins[ $value ] = false;
		}
		$this->installedPlugins[ SUPPORTED_PLUGINS['WordPress'] ] = true;

		$this->pluginDir        = Helper::pluginDirPath( realpath( __DIR__ . '/../..' ) );
		$this->pluginUrl        = Helper::pluginDirUrl( realpath( __DIR__ . '/../..' ) );
		$this->Configuration    = Configuration::getInstance();
		$this->PlatformSettings = PlatformSettings::getInstance();
		$this->CronController    = new CronController();
	}

    /**
	 * @param $request
	 * @return $this|false
	 */
	public function parseSettingsFromRequest( $request ) {
		if (empty( $request['page'] )) {
			return false;
		}
		$page = $request['page'];

		/* MAIN = INTEGRATIONS SETTINGS PAGE */
		if ( $page == 'salesmanago' ) {
			$contactCookieTtl = AbstractEntity::DEFAULT_CONTACT_COOKIE_TTL;
			if ( ( isset( $request['contact-cookie-ttl-active'] ) && boolval( $request['contact-cookie-ttl-active'] ) )
				&& ( ! empty( $request['contact-cookie-ttl'] ) || $request['contact-cookie-ttl'] === '0' ) ) {
				$contactCookieTtl = (int)( (float) self::validateContactCookieTtl($request['contact-cookie-ttl']) * 24 * 60 * 60 );
			}

			$this->getConfiguration()
				->setIgnoredDomains( Helper::clearCSVInput( $request['salesmanago-ignored-domains'], false ) )
				->setLocation( ! empty( $request['salesmanago-location'] ) ? self::validateLocation($request['salesmanago-location']) : Helper::getLocation() )
				->setContactCookieTtl( $contactCookieTtl );

			$this->getPlatformSettings()
				->setLanguageDetection(
					isset( $request['language-detection'] )
						? $request['language-detection']
						: 'platform'
				);

			$this->getPlatformSettings()
				->setCronEnabled(
					isset( $request[ 'cron-active' ] ) ? $request[ 'cron-active' ] : false
				);

			if ( $this->getPlatformSettings()->getCronEnabled() ) {
				$cronFrequency = ( $request[ 'cron-frequency' ] ?? 0 );

				switch ( $cronFrequency ) {
					case 'one-minute':
						$cronValue = CronController::CRON_SCHEDULE_KEY_60;
						break;
					case 'three-minutes':
						$cronValue = CronController::CRON_SCHEDULE_KEY_180;
						break;
					case 'five-minutes':
						$cronValue = CronController::CRON_SCHEDULE_KEY_300;
						break;
					default:
						$cronValue = 0;
				}

					$this->getPlatformSettings()->setCronValue( $cronValue );
					$this->CronController->update_cron_schedule( $cronValue );
				} else {
					$this->getPlatformSettings()->setCronValue( 0 );
					$this->CronController->update_cron_schedule( '0' );
            }


		}
		/* MONITCODE PAGE */
		elseif ( $page == 'salesmanago-monit-code' ) {
			$this->getPlatformSettings()
				->getMonitCode()
					->setDisableMonitoringCode( ! empty( $request['salesmanago-monitcode-disable-monitoring-code'] ) )
					->setSmCustom( ! empty( $request['salesmanago-monitcode-smcustom'] ) )
					->setSmBanners( ! empty( $request['salesmanago-monitcode-smbanners'] ) )
					->setPopUpJs( ! empty( $request['salesmanago-monitcode-popup-js'] ) );
		}

		/* PLUGINS PAGE */
		elseif ( $page == 'salesmanago-plugins' ) {
			$PlatformSettings = $this->getPlatformSettings();
			$PlatformSettings->getPluginWp()->setActive( isset( $request['salesmanago-plugin-wp'] ) );
			$PlatformSettings->getPluginWc()->setActive( isset( $request['salesmanago-plugin-wc'] ) );
			$PlatformSettings->getPluginCf7()->setActive( isset( $request['salesmanago-plugin-cf7'] ) );
			$PlatformSettings->getPluginGf()->setActive( isset( $request['salesmanago-plugin-gf'] ) );
			$PlatformSettings->getPluginFf()->setActive( isset( $request['salesmanago-plugin-ff'] ) );
			if ( $PlatformSettings->isActive( SUPPORTED_PLUGINS['WordPress'] )
				&& $PlatformSettings->isActive( SUPPORTED_PLUGINS['WooCommerce'] ) ) {
				$PlatformSettings->getPluginWp()->setActive( false );
			}
		}

		/* WP SETTINGS PAGE */
		elseif ( $page == 'salesmanago-plugin-wp' ) {
			$this->getPlatformSettings()->getPluginWp()
				->setTags( isset( $request['tags'] ) ? $request['tags'] : null )
				->setOwner( isset( $request['owner'] ) ? $request['owner'] : null )
				->getDoubleOptIn()
					->setDoubleOptIn( isset( $request['double-opt-in'] ) ? $request['double-opt-in'] : array() );
			$this->getPlatformSettings()->getPluginWp()
				->getOptInInput()
					->setOptInInput( isset( $request['opt-in-input'] ) ? $request['opt-in-input'] : array() );
			$this->getPlatformSettings()->getPluginWp()
				->getOptInMobileInput()
					->setOptInInput( isset( $request['opt-in-mobile-input'] ) ? $request['opt-in-mobile-input'] : array(), true );
		}

		/* WC SETTINGS PAGE */
		elseif ( $page == 'salesmanago-plugin-wc' ) {
			$this->getConfiguration()
				->setEventCookieTtl(
					isset( $request['event-cookie-ttl'] )
						? (int) $request['event-cookie-ttl']
						: Configuration::DEFAULT_EVENT_COOKIE_TTL
				);

			$this->getPlatformSettings()->getPluginWc()
				->setTags( isset( $request['tags'] ) ? $request['tags'] : null )
				->setOwner( isset( $request['owner'] ) ? $request['owner'] : null )
				->setProductIdentifierType( isset( $request['product-identifier-type'] ) ? $request['product-identifier-type'] : null )
				->setPurchaseHook( isset( $request['purchase-hook'] ) ? $request['purchase-hook'] : null )
				->setPreventEventDuplication( isset( $request['prevent-event-duplication'] ) ? $request['prevent-event-duplication'] : false )
				->getDoubleOptIn()
					->setDoubleOptIn( isset( $request['double-opt-in'] ) ? $request['double-opt-in'] : array() );
			$this->getPlatformSettings()->getPluginWc()
				->getOptInInput()
					->setOptInInput( isset( $request['opt-in-input'] ) ? $request['opt-in-input'] : array() );
			$this->getPlatformSettings()->getPluginWc()
				 ->getOptInMobileInput()
					->setOptInInput( isset( $request['opt-in-mobile-input'] ) ? $request['opt-in-mobile-input'] : array(), true );
		}

		/* CF7 SETTINGS PAGE */
		elseif ( $page == 'salesmanago-plugin-cf7' ) {
			$this->getPlatformSettings()->getPluginCf7()
				->setProperties( isset( $request['custom-properties'] ) ? $request['custom-properties'] : null )
				->deleteForms() // Remove forms not sent in request (those removed with a button)
				->setFormsFromRequest( isset( $request['salesmanago-forms'] ) ? $request['salesmanago-forms'] : null )
				->setPropertiesMappingMode( isset( $request['salesmanago-properties-type'] ) ? $request['salesmanago-properties-type'] : null )
				->getDoubleOptIn()
					->setDoubleOptIn( isset( $request['double-opt-in'] ) ? $request['double-opt-in'] : array() );
		}

		/* GF SETTINGS PAGE */
		elseif ( $page == 'salesmanago-plugin-gf' ) {
			$this->getPlatformSettings()->getPluginGf()
				->setProperties( isset( $request['custom-properties'] ) ? $request['custom-properties'] : null )
				->deleteForms() // Remove forms not sent in request (those removed with a button)
				->setFormsFromRequest( isset( $request['salesmanago-forms'] ) ? $request['salesmanago-forms'] : null )
				->setPropertiesMappingMode( isset( $request['salesmanago-properties-type'] ) ? $request['salesmanago-properties-type'] : null )
				->getDoubleOptIn()
					->setDoubleOptIn( isset( $request['double-opt-in'] ) ? $request['double-opt-in'] : array() );
		}

		/* FF SETTING PAGE */
		elseif ( $page == 'salesmanago-plugin-ff' ) {
			$this->getPlatformSettings()->getPluginFf()
				->setProperties( isset( $request['custom-properties'] ) ? $request['custom-properties'] : null )
				->deleteForms() // Remove forms not sent in request (those removed with a button)
				->setFormsFromRequest( isset( $request['salesmanago-forms'] ) ? $request['salesmanago-forms'] : null )
				->setPropertiesMappingMode( isset( $request['salesmanago-properties-type'] ) ? $request['salesmanago-properties-type'] : null )
				->getDoubleOptIn()
					->setDoubleOptIn( isset( $request['double-opt-in'] ) ? $request['double-opt-in'] : array() );
		}
		return $this;
	}

    /**
     * Create cron table if not already in database
     *
     * @return void
     */
	public function insertCronTableIfNotExist() {
		try {
			$cron = $this->db->get_row( $this->db->prepare( "SELECT option_value FROM {$this->db->options} WHERE option_name = %s LIMIT 1", self::CRON_CONFIGURATION ), ARRAY_A );

			if ( empty( $cron ) ) {
				$this->db->query( $this->db->prepare( "INSERT INTO {$this->db->options} (option_id, option_name, option_value) VALUES (NULL, %s, %s)", array( self::CRON_CONFIGURATION, [] ) ) );
			}
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( $e->setCode( 501 ) );
		} catch ( \Exception $e ) {
			MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 501 ) );
		}
	}

	/**
	 * @return $this|false
	 */
	public function getConfigurationFromDb() {
		try {
			$stmt = $this->db->get_row( $this->db->prepare( "SELECT option_value FROM {$this->db->options} WHERE option_name = %s LIMIT 1", self::CONFIGURATION ), ARRAY_A );

			if ( empty( $stmt ) ) {
				$this->db->query( $this->db->prepare( "INSERT INTO {$this->db->options} (option_id, option_name, option_value) VALUES (NULL, %s, %s)", array( self::CONFIGURATION, '' ) ) );
			}
			if ( empty( $stmt ) || empty( $stmt['option_value'] ) || $stmt['option_value'] == '{}' ) {
				$stmt = $this->db->get_row( $this->db->prepare( "SELECT option_value FROM {$this->db->options} WHERE option_name = %s LIMIT 1", 'salesmanago_settings' ), ARRAY_A );
				if ( $stmt == null ) {
					return $this;
				}
				$conf = json_decode( $stmt['option_value'] );
				$this->setLegacyConfiguration( $conf );
				$this->saveConfiguration();
				return $this;
			}

			$conf = json_decode( $stmt['option_value'] );

			$this->setConfiguration( $conf );
			return $this;

		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( $e->setCode( 501 ) );
		} catch ( \Exception $e ) {
			MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 501 ) );
		}
		return null;
	}

	/**
	 * @param $conf
	 */
	private function setConfiguration( $conf ) {
		$this->Configuration
			->setClientId( isset( $conf->clientId ) ? $conf->clientId : '' )
			->setApiKey( isset( $conf->apiKey ) ? $conf->apiKey : '' )
			->setSha( isset( $conf->sha ) ? $conf->sha : '' )
			->setToken( isset( $conf->token ) ? $conf->token : '' )
			->setEndpoint( isset( $conf->endpoint ) ? $conf->endpoint : '' )
			->setOwner( isset( $conf->owner ) ? $conf->owner : '' )
			->setContactCookieTtl( ! empty( $conf->contactCookieTtl ) ? $conf->contactCookieTtl : '' )
			->setEventCookieTtl( isset( $conf->eventCookieTtl ) ? $conf->eventCookieTtl : '' )
			->setIgnoredDomains( isset( $conf->ignoredDomains ) ? $conf->ignoredDomains : array() )
			->setOwnersList( isset( $conf->ownersList ) ? $conf->ownersList : array() )
			->setActive( isset( $conf->active ) ? $conf->active : false )
			->setLocation( isset( $conf->location ) ? $conf->location : Helper::getLocation() )
            ->setSmApp(isset( $conf->smApp ) ? $conf->smApp : 0)
			->setPlatformName( self::platformName )
            ->setApiV3Key( isset( $conf->apiV3Key ) ? $conf->apiV3Key : '' )
            ->setApiV3Endpoint( isset ( $conf->apiV3Endpoint) ? $conf->apiV3Endpoint : 'https://api.salesmanago.com' )
            ->setCatalogs( isset ( $conf->Catalogs) ? $conf->Catalogs : '' )
            ->setActiveCatalog( isset ( $conf->activeCatalog ) ? $conf->activeCatalog : '' )
			->setisNewApiError( $conf->isNewApiError ?? false );
	}

	/**
	 * @param $conf
	 * @throws Exception
	 */
	private function setLegacyConfiguration( $conf ) {
		$this->setConfiguration( $conf );

		$ignoredDomains = array_unique(
			array_merge(
				! empty( $conf->extensions->wp->ignoreDomain ) ? explode( ',', $conf->extensions->wp->ignoreDomain ) : array(),
				! empty( $conf->extensions->wc->ignoreDomain ) ? explode( ',', $conf->extensions->wc->ignoreDomain ) : array(),
				! empty( $conf->extensions->cf7->ignoreDomain ) ? (array) $conf->extensions->cf7->ignoreDomain : array(),
				! empty( $conf->extensions->gf->ignoreDomain ) ? (array) $conf->extensions->gf->ignoreDomain : array()
			)
		);
		$this->Configuration
			->setActive( true )
			->setIgnoredDomains( Helper::filterArray( $ignoredDomains ) )
			->setLocation( isset( $conf->location ) ? $conf->location : Helper::getLocation() );
	}

	/**
	 * @return $this
	 */
	public function saveConfiguration() {
		try {
			$json = json_encode( $this->getConfiguration() );
			$this->db->query( $this->db->prepare( "UPDATE {$this->db->options} SET option_value = %s WHERE option_name = %s", array( $json, self::CONFIGURATION ) ) );
			return $this;
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( $e->setCode( 503 ) );
		} catch ( \Exception $e ) {
			MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 503 ) );
		}
		return null;
	}

	/**
	 * @return $this
	 */
	public function getPlatformSettingsFromDb() {
		try {
			$stmt = $this->db->get_row( $this->db->prepare( "SELECT option_value FROM {$this->db->options} WHERE option_name = %s LIMIT 1", self::PLATFORM_SETTINGS ), ARRAY_A );

			if ( empty( $stmt ) ) {
				$this->db->query( $this->db->prepare( "INSERT INTO {$this->db->options} (option_id, option_name, option_value) VALUES (NULL, %s, %s)", array( self::PLATFORM_SETTINGS, '' ) ) );
			}
			if ( empty( $stmt ) || empty( $stmt['option_value'] ) || $stmt['option_value'] == '{}' ) {
				$this->setPlatformSettings( self::getDefaultPlatformSettings() );
				$stmt = $this->db->get_row( $this->db->prepare( "SELECT option_value FROM {$this->db->options} WHERE option_name = %s LIMIT 1", 'salesmanago_settings' ), ARRAY_A );
				if ( ! empty( $stmt ) ) {
					$platformSettings = json_decode( $stmt['option_value'] );
					$this->setLegacyPlatformSettings( $platformSettings );
				}

				$this->savePlatformSettings();
				return $this;
			}
			$platformSettings = json_decode( $stmt['option_value'] );

			$this->setPlatformSettings( $platformSettings );
			return $this;
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( $e->setCode( 502 ) );
		} catch ( \Exception $e ) {
			MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 502 ) );
		}
		return null;
	}

    /**
     * @param $settings
     */
	private function setPlatformSettings( $settings ) {
		$PlatformSettings = $this->getPlatformSettings();

		$PlatformSettings
			->setLanguageDetection(
				isset( $settings->languageDetection )
				? $settings->languageDetection
				: 'platform'
			)
			->setPluginVersion(
				isset( $settings->pluginVersion )
				? $settings->pluginVersion
				: '3.1.0'
			);

		$PlatformSettings->setDetailsMapping( $settings->DetailsMapping ?? array() );
		$PlatformSettings->setCronEnabled( (bool) $settings->cronEnabled ?? false );
		$PlatformSettings->setCronValue( $settings->cronValue ?? 0 );
		$PlatformSettings->getMonitCode()->setPluginSettings( $settings->MonitCode ?? null );
		$PlatformSettings->getPluginWp()->setPluginSettings( $settings->PluginWp ?? null );
		$PlatformSettings->getPluginWc()->setPluginSettings( $settings->PluginWc ?? null );
		$PlatformSettings->getPluginCf7()->setPluginSettings( $settings->PluginCf7 ?? null );
		$PlatformSettings->getPluginGf()->setPluginSettings( $settings->PluginGf ?? null );
		$PlatformSettings->getPluginFf()->setPluginSettings( $settings->PluginFf ?? null );
	}

	/**
	 * @param $settings
	 */
	private function setLegacyPlatformSettings( $settings ) {
		$PlatformSettings = $this->getPlatformSettings();

		$PlatformSettings->setPluginVersion( '2.7.0' );

		$PlatformSettings->getMonitCode()->setDisableMonitoringCode( false );
		$PlatformSettings->getPluginWp()
			->setActive( isset( $settings->extensions->active->wp ) && $settings->extensions->active->wp )
			->setTags( isset( $settings->extensions->wp->tags ) ? (array) $settings->extensions->wp->tags : array() )
			->setOwner( isset( $settings->owner ) ? $settings->owner : '' )
			->getOptInInput()
				->setLegacyOptInInput(
					isset( $settings->extensions->news ) ? $settings->extensions->news : null,
					isset( $settings->extensions->active->news ) && $settings->extensions->active->news
				);

		$PlatformSettings->getPluginWp()->getDoubleOptIn()
			->setActive( isset( $settings->apiDoubleOptIn ) && $settings->apiDoubleOptIn )
			->setEmailId( $settings->doubleOptIn->emailId ?? '' )
			->setTemplateId( isset( $settings->doubleOptIn->template ) ? $settings->doubleOptIn->template : '' )
			->setAccountId( isset( $settings->doubleOptIn->email ) ? $settings->doubleOptIn->email : '' )
			->setSubject( isset( $settings->doubleOptIn->topic ) ? $settings->doubleOptIn->topic : '' );

		$PlatformSettings->getPluginWc()
			->setActive( isset( $settings->extensions->active->wc ) && ( $settings->extensions->active->wc ) )
			->setTags( isset( $settings->extensions->wc->tags ) ? (array) $settings->extensions->wc->tags : array() )
			->setOwner( isset( $settings->owner ) ? $settings->owner : '' )
			->setPurchaseHook(
				isset( $settings->extensions->wc->event_config->hookConfig )
					? $settings->extensions->wc->event_config->hookConfig
					: Wc::DEFAULT_PURCHASE_HOOK
			)
			->getOptInInput()
				->setLegacyOptInInput(
					isset( $settings->extensions->news ) ? $settings->extensions->news : null,
					isset( $settings->extensions->active->news ) && $settings->extensions->active->news
				);

		$PlatformSettings->getPluginWc()->getDoubleOptIn()
			->setActive( isset( $settings->apiDoubleOptIn ) && $settings->apiDoubleOptIn )
			->setEmailId( $settings->doubleOptIn->emailId ?? '' )
			->setTemplateId( isset( $settings->doubleOptIn->template ) ? $settings->doubleOptIn->template : '' )
			->setAccountId( isset( $settings->doubleOptIn->email ) ? $settings->doubleOptIn->email : '' )
			->setSubject( isset( $settings->doubleOptIn->topic ) ? $settings->doubleOptIn->topic : '' );

		$PlatformSettings->getPluginCf7()
			->setActive(
				isset( $settings->extensions->active->cf7 ) && $settings->extensions->active->cf7
			)
			->setLegacyFormsCf7(
				isset( $settings->extensions->cf7->form )
					? (array) $settings->extensions->cf7->form
					: array()
			)
			->setLegacyProperties(
				isset( $settings->extensions->cf7->properties ) ? (array) $settings->extensions->cf7->properties : array(),
				isset( $settings->extensions->cf7->options ) ? (array) $settings->extensions->cf7->options : array()
			)
			->setPropertiesMappingMode( AbstractPlugin::DEFAULT_PROPERTY_TYPE )
			->getDoubleOptIn()->setLegacyDoubleOptIn(
				isset( $settings->extensions->cf7->confirmation )
					? (array) $settings->extensions->cf7->confirmation
					: null
			);

		$PlatformSettings->getPluginGf()
			->setActive( isset( $settings->extensions->active->gf ) && $settings->extensions->active->gf )
			->setLegacyFormsGf( isset( $settings->extensions->gf->form ) ? (array) $settings->extensions->gf->form : array() )
			->setPropertiesMappingMode( AbstractPlugin::DEFAULT_PROPERTY_TYPE )
			->getDoubleOptIn()->setLegacyDoubleOptIn(
				isset( $settings->extensions->gf->confirmation )
					? (array) $settings->extensions->gf->confirmation
					: null
			);

		$PlatformSettings->getPluginFf()
			->setPropertiesMappingMode( AbstractPlugin::DEFAULT_PROPERTY_TYPE );
	}

	/**
	 * @return $this
	 */
	public function savePlatformSettings() {
		try {
			$this->getPlatformSettings()->setUpdatedAt( time() );

			$json = json_encode( $this->getPlatformSettings() );
			$this->db->query( $this->db->prepare( "UPDATE {$this->db->options} SET option_value = %s WHERE option_name = %s", array( $json, self::PLATFORM_SETTINGS ) ) );

			try {
				$ReportingController = new ReportingController( $this );
				$ReportingController->reportUserAction( ReportingController::ACTION_SETTINGS_SAVED );
			} catch ( \Exception $e ) {
				error_log( $e->getMessage() );
			}

			return $this;
		} catch ( Exception $e ) {
			MessageEntity::getInstance()->addException( $e->setCode( 504 ) );
		} catch ( \Exception $e ) {
			MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 504 ) );
		}
		return null;
	}

	/**
	 * @return stdClass
	 */
	private static function getDefaultPlatformSettings() {
		$platformSettings = new stdClass();

		$platformSettings->PluginWp  = new stdClass();
		$platformSettings->PluginWc  = new stdClass();
		$platformSettings->PluginCf7 = new stdClass();
		$platformSettings->PluginGf  = new stdClass();
		$platformSettings->PluginFf  = new stdClass();
		$platformSettings->MonitCode = new stdClass();

		$platformSettings->PluginWp->tags = new stdClass();
		$platformSettings->PluginWc->tags = new stdClass();

		$platformSettings->PluginWp->tags->login        = 'wp_login';
		$platformSettings->PluginWp->tags->registration = 'wp_register';
		$platformSettings->PluginWp->tags->newsletter   = 'wp_newsletter';

		$platformSettings->PluginWc->tags->login             = 'wc_login';
		$platformSettings->PluginWc->tags->registration      = 'wc_register';
		$platformSettings->PluginWc->tags->newsletter        = 'wc_newsletter';
		$platformSettings->PluginWc->tags->purchase          = 'wc_purchase';
		$platformSettings->PluginWc->tags->guestPurchase     = 'wc_guest_purchase';
		$platformSettings->PluginWc->productIdentifierType   = 'id';
		$platformSettings->PluginWc->preventEventDuplication = false;

		$platformSettings->PluginCf7->propertiesType = AbstractPlugin::DEFAULT_PROPERTY_TYPE;
		$platformSettings->PluginFf->propertiesType  = AbstractPlugin::DEFAULT_PROPERTY_TYPE;
		$platformSettings->PluginGf->propertiesType  = AbstractPlugin::DEFAULT_PROPERTY_TYPE;

		$platformSettings->MonitCode->disableMonitoringCode = false;
		$platformSettings->MonitCode->smCustom              = false;
		$platformSettings->MonitCode->smBanners             = false;
		$platformSettings->MonitCode->popUpJs               = false;

		return $platformSettings;
	}

	/**
	 * @return string
	 */
	public function getDefaultExportTags() {
		return ! empty( self::EXPORT_TAGS ) ? self::EXPORT_TAGS : 'WP_EXPORT';
	}

	/**
	 * @param string $pluginDir
	 * @return $this
	 */
	public function setPluginDir( $pluginDir = '' ) {
		if ( ! empty( $pluginDir ) ) {
			$this->pluginDir = $pluginDir;
		} else {
			$this->pluginDir = Helper::pluginDirPath( realpath( __DIR__ . '/../..' ) );
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPluginDir() {
		return $this->pluginDir;
	}

	/**
	 * @param string $pluginUrl
	 * @return $this
	 */
	public function setPluginUrl( $pluginUrl = '' ) {
		if ( ! empty( $pluginUrl ) ) {
			$this->pluginUrl = $pluginUrl;
		} else {
			$this->pluginUrl = Helper::pluginDirUrl( realpath( __DIR__ . '/../..' ) );
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPluginUrl() {
		return $this->pluginUrl;
	}

	/**
	 * @param false $userLogged
	 * @return $this
	 */
	public function setUserLogged( $userLogged = false ) {
		$this->userLogged = boolval( $userLogged );
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getUserLogged() {
		return $this->userLogged;
	}

	/**
	 * @param string $page
	 * @return $this
	 */
	public function setPage( $page = 'salesmanago' ) {
		$this->page = $page;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * @param array $availableTabs
	 * @return $this
	 */
	public function setAvailableTabs( array $availableTabs ) {
		$this->availableTabs = $availableTabs;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getAvailableTabs() {
		return $this->availableTabs;
	}

	/**
	 * @param $tab
	 */
	public function appendAvailableTabs( $tab ) {
		if ( is_array( $tab ) ) {
			$this->availableTabs = array_merge(
				$this->availableTabs,
				$tab
			);
		} else {
			$this->availableTabs[] = $tab;
		}
	}

	/**
	 * @param $tab
	 * @return bool
	 */
	public function isTabAvailable( $tab ) {
		return in_array( $tab, $this->availableTabs );
	}


	/**
	 * @return mixed|Configuration
	 */
	public function getConfiguration() {
		return $this->Configuration;
	}

	/**
	 * @return PlatformSettings|mixed
	 */
	public function getPlatformSettings() {
		return $this->PlatformSettings;
	}

	/**
	 * @param $Settings
	 */
	public function setSettings( $Settings ) {
		$this->Settings = $Settings;
	}

	/**
	 * @return bool
	 */
	public static function isDefaultContactCookieLifetime() {
		return Configuration::DEFAULT_CONTACT_COOKIE_TTL === Configuration::getInstance()->getContactCookieTtl();
	}

	/**
	 *
	 */
	public function removeSettingsOnLogout() {
		if ( empty( $this->getConfiguration() ) ) {
			$this->getConfigurationFromDb();
		}
		$this->getConfiguration()
			->setClientId( '' )
			->setEndpoint( '' )
			->setToken( '' )
			->setSha( '' )
			->setOwner( '' )
			->setSmApp(0)
			->setApiKey( '' )
			->setApiV3Key( '' )
			->setApiV3Endpoint( 'https://api.salesmanago.com' )
			->setCatalogs( '' )
			->setActiveCatalog( '' )
			->setisNewApiError( false );

		$this->saveConfiguration();
	}

	/**
	 * @param $name
	 * @return false|mixed
	 */
	public function getInstalledPluginByName( $name ) {
		return isset( $this->installedPlugins[ $name ] )
			? $this->installedPlugins[ $name ]
			: false;
	}

	/**
	 * @return string
	 */
	public static function getIconBase64() {
		return 'data:image/svg+xml;base64,' . 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOmNjPSJodHRwOi8vY3JlYXRpdmVjb21tb25zLm9yZy9ucyMiIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiIHhtbG5zOmlua3NjYXBlPSJodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy9uYW1lc3BhY2VzL2lua3NjYXBlIiB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCAxNi45MzMzMzMgMTYuOTMzMzM0IiB2ZXJzaW9uPSIxLjEiIGlkPSJzdmc0MDE5IiBzb2RpcG9kaTpkb2NuYW1lPSJpY29uLnN2ZyIgaW5rc2NhcGU6dmVyc2lvbj0iMS4wLjEgKDA3NjdmODMwMmEsIDIwMjAtMTAtMTcpIj4gPHNvZGlwb2RpOm5hbWVkdmlldyBwYWdlY29sb3I9IiNmZmZmZmYiIGJvcmRlcmNvbG9yPSIjNjY2NjY2IiBib3JkZXJvcGFjaXR5PSIxIiBvYmplY3R0b2xlcmFuY2U9IjEwIiBncmlkdG9sZXJhbmNlPSIxMCIgZ3VpZGV0b2xlcmFuY2U9IjEwIiBpbmtzY2FwZTpwYWdlb3BhY2l0eT0iMCIgaW5rc2NhcGU6cGFnZXNoYWRvdz0iMiIgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIxODQ4IiBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSIxMDE2IiBpZD0ibmFtZWR2aWV3NDE2MCIgc2hvd2dyaWQ9ImZhbHNlIiBpbmtzY2FwZTp6b29tPSI1LjI1NzgxMjUiIGlua3NjYXBlOmN4PSItMTYuNjgxNDU3IiBpbmtzY2FwZTpjeT0iNTAuOTA5MDIyIiBpbmtzY2FwZTp3aW5kb3cteD0iNzIiIGlua3NjYXBlOndpbmRvdy15PSIyNyIgaW5rc2NhcGU6d2luZG93LW1heGltaXplZD0iMSIgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0ic3ZnNDAxOSIgLz4gPGRlZnMgaWQ9ImRlZnM0MDEzIiAvPiA8bWV0YWRhdGEgaWQ9Im1ldGFkYXRhNDAxNiI+IDxyZGY6UkRGPiA8Y2M6V29yayByZGY6YWJvdXQ9IiI+IDxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PiA8ZGM6dHlwZSByZGY6cmVzb3VyY2U9Imh0dHA6Ly9wdXJsLm9yZy9kYy9kY21pdHlwZS9TdGlsbEltYWdlIiAvPiA8ZGM6dGl0bGUgLz4gPC9jYzpXb3JrPiA8L3JkZjpSREY+IDwvbWV0YWRhdGE+IDxwYXRoIGlkPSJwYXRoNDE2MiIgc3R5bGU9ImZpbGw6IzllYTJhOTtzdHJva2Utd2lkdGg6MS45MDM2MTtzdHJva2UtZGFzaGFycmF5OjEuOTAzNjEsIDUuNzEwODMiIGQ9Ik0gMzIgMCBBIDMyLjAwMDAwMiAzMi4wMDAwMDIgMCAwIDAgMCAzMiBBIDMyLjAwMDAwMiAzMi4wMDAwMDIgMCAwIDAgMzIgNjQgQSAzMi4wMDAwMDIgMzIuMDAwMDAyIDAgMCAwIDY0IDMyIEEgMzIuMDAwMDAyIDMyLjAwMDAwMiAwIDAgMCAzMiAwIHogTSA0Ny41MjE0ODQgOC4yNTM5MDYyIEMgNDcuNTc4MjUzIDguMjUzOTA2MiA0Ny41OTk2MDkgMTQuMTYwMzUgNDcuNTk5NjA5IDI3Ljg0NTcwMyBDIDQ3LjU5OTYwOSA0Ni40MTI2ODIgNDcuNTk0NjcyIDQ3LjQ0NjggNDcuNDcwNzAzIDQ3LjU1NjY0MSBDIDQ3LjM5OTUzNSA0Ny42MjA3NDEgNDcuMDc1NDgyIDQ3Ljg1MDY4IDQ2Ljc1IDQ4LjA3MDMxMiBDIDQ2LjQyNDc3MiA0OC4yODk4MjcgNDUuMDEyMTUzIDQ5LjI2MjkzNSA0My42MTEzMjggNTAuMjMyNDIyIEMgNDIuMjEwNDQ2IDUxLjIwMTkzMSA0MC44ODE1NjYgNTIuMTIyODcxIDQwLjY1ODIwMyA1Mi4yNzczNDQgQyA0MC40MzQ0OTMgNTIuNDMxNyA0MC4yMTA5MTIgNTIuNTU4NTk0IDQwLjE2MDE1NiA1Mi41NTg1OTQgQyA0MC4wODY5MDkgNTIuNTU4NyA0MC4wNjgzNTkgNDguNTg4MzcgNDAuMDY4MzU5IDMyLjk1NzAzMSBMIDQwLjA2ODM1OSAxMy4zNTU0NjkgTCA0MC41ODM5ODQgMTMuMDAxOTUzIEMgNDAuODY4MDkxIDEyLjgwNzAzMiA0MS4zMTUyMTEgMTIuNDk3MjM2IDQxLjU3NjE3MiAxMi4zMTQ0NTMgQyA0MS44MzcwMzUgMTIuMTMxNTY2IDQyLjI2ODg3MyAxMS44MzMxMjEgNDIuNTM3MTA5IDExLjY1MDM5MSBDIDQyLjgwNTUzMSAxMS40Njc1MDMgNDMuMTkwMjM0IDExLjIwMDc3IDQzLjM5MDYyNSAxMS4wNTg1OTQgQyA0My41OTEwNTMgMTAuOTE2MjYxIDQzLjk2MzYyMSAxMC42NTc2NDMgNDQuMjE4NzUgMTAuNDgyNDIyIEMgNDQuNDc0MDk1IDEwLjMwNzEyNCA0NS4zMDUxNzMgOS43MzIxNzEzIDQ2LjA2NDQ1MyA5LjIwNzAzMTIgQyA0Ni44MjM2ODUgOC42ODE4NjUxIDQ3LjQ3ODYyNSA4LjI1MzkwNjIgNDcuNTIxNDg0IDguMjUzOTA2MiB6IE0gMzQuNDE3OTY5IDE3LjI4OTA2MiBDIDM0LjUxODcwMSAxNy4yNDg3NjkgMzQuNTMwNjA1IDE4LjgzNzM5NiAzNC41MjkyOTcgMzIuMzM1OTM4IEMgMzQuNTI4NzIxIDQwLjYzNTQwNSAzNC41MDI0ODQgNDcuNDYwNzM2IDM0LjQ3MjY1NiA0Ny41MDM5MDYgQyAzNC40NDEyNDggNDcuNTQ4NjU2IDMzLjg4NjQwMiA0Ny45NDQ0OTEgMzMuMjM2MzI4IDQ4LjM4NjcxOSBDIDMyLjU4NjQ2NCA0OC44Mjg2ODUgMzEuODE0Njc5IDQ5LjM1NzM3NCAzMS41MjE0ODQgNDkuNTYyNSBDIDMxLjIyODQ3MyA0OS43Njc4OCAzMC43MTMzMTkgNTAuMTI1MjkxIDMwLjM3Njk1MyA1MC4zNTc0MjIgQyAzMC4wNDA1MzQgNTAuNTg5NDg1IDI5LjE5MDE5NiA1MS4xODExNSAyOC40ODYzMjggNTEuNjY5OTIyIEMgMjcuNzgyNDYxIDUyLjE1ODY0MSAyNy4xNzU4NTEgNTIuNTU4NTk0IDI3LjEzODY3MiA1Mi41NTg1OTQgQyAyNy4wOTgzNzggNTIuNTU4NTk0IDI3LjA3MDMxMyA0Ni41Njg4OTIgMjcuMDcwMzEyIDM3LjQzNTU0NyBMIDI3LjA3MjI2NiAzNy40MzU1NDcgTCAyNy4wNzIyNjYgMjIuMzEyNSBMIDI4LjY0MDYyNSAyMS4yMzI0MjIgQyAyOS41MDM3MDEgMjAuNjM3NzkgMzAuNDQyMzE1IDE5Ljk4OTM4MyAzMC43MjY1NjIgMTkuNzkyOTY5IEMgMzIuOTkwNzc5IDE4LjIyODMwMiAzNC4zMjI2IDE3LjMyNTcxOSAzNC40MTc5NjkgMTcuMjg5MDYyIHogTSAyMS4zOTg0MzggMjYuMjY5NTMxIEMgMjEuNDI5ODQ1IDI2LjI2OTUzMSAyMS40NjA5MzggMzAuOTk2MjY5IDIxLjQ2MDkzOCAzNi43NzM0MzggTCAyMS40NjA5MzggNDcuMjc3MzQ0IEwgMjEuMTExMzI4IDQ3LjUxMzY3MiBDIDIwLjYxOTIzMyA0Ny44NDY5NTEgMTkuNTMzMjYxIDQ4LjU5ODgxMyAxNy4wNzgxMjUgNTAuMzA0Njg4IEMgMTMuOTcwMTY3IDUyLjQ2NDE0MyAxNC4wMDYzODMgNTIuNDQyMjExIDE0LjAwNTg1OSA1Mi4yNDIxODggTCAxNC4wMDE5NTMgNTIuMjQyMTg4IEMgMTQuMDAxNzE4IDUyLjE1MTQwMyAxNC4wMDAzNjcgNDcuNDA5MjA2IDE0IDQxLjcwMzEyNSBDIDEzLjk5OTc2NSAzMi4zNzgwNSAxNC4wMTM0NTIgMzEuMzIwMzExIDE0LjExNTIzNCAzMS4yNTM5MDYgQyAxNC4yMzA2MiAzMS4xNzk2MDEgMTcuMjM1MDY5IDI5LjEwOTMwOCAxNy45ODgyODEgMjguNTgzOTg0IEMgMTguMjEwOTM3IDI4LjQyODgzMSAxOC44NTg0MjkgMjcuOTgyOTE1IDE5LjQyNzczNCAyNy41OTM3NSBDIDE5Ljk5Njg1NiAyNy4yMDQ1NTggMjAuNjYwMTI1IDI2Ljc0NzM1NCAyMC45MDAzOTEgMjYuNTc4MTI1IEMgMjEuMTQxMTAxIDI2LjQwOTEwNSAyMS4zNjQ4MTcgMjYuMjY5NTMxIDIxLjM5ODQzOCAyNi4yNjk1MzEgeiAiIHRyYW5zZm9ybT0ic2NhbGUoMC4yNjQ1ODMzNCkiIC8+PC9zdmc+';
	}

	/**
	 * @return array
	 */
	public function getInstalledPlugins() {
		return $this->installedPlugins;
	}

	/**
	 * @param null $installedPlugins
	 */
	public function setInstalledPlugins( $installedPlugins = null ) {
		if ( empty( $installedPlugins ) ) {
			if ( Helper::isPluginActive( 'woocommerce/woocommerce.php' ) ) {
				$this->installedPlugins[ SUPPORTED_PLUGINS['WooCommerce'] ] = true;
			}
			if ( Helper::isPluginActive( 'contact-form-7/wp-contact-form-7.php' ) ) {
				$this->installedPlugins[ SUPPORTED_PLUGINS['Contact Form 7'] ] = true;
			}
			if ( Helper::isPluginActive( 'gravityforms/gravityforms.php' ) ) {
				$this->installedPlugins[ SUPPORTED_PLUGINS['Gravity Forms'] ] = true;
			}
			if ( Helper::isPluginActive( 'fluentform/fluentform.php' ) ) {
				$this->installedPlugins[ SUPPORTED_PLUGINS['Fluent Forms'] ] = true;
			}
		} else {
			$this->installedPlugins = $installedPlugins;
		}
	}

	/**
	 * @return false|int
	 */
	public function getPluginInstalledDate() {
		try {
			$date = filemtime( $this->pluginDir . '/readme.txt' );
		} catch ( \Exception $e ) {
			return false;
		}
		return $date;
	}

	/**
	 * @param $params
	 * @return string
	 */
	public function buildOptions( $params ) {
		if ( is_string( $params ) ) {
			return '<option value="' . $params . '>' . $params . '</option>';
		} elseif ( is_array( $params ) ) {
			$result = '';
			foreach ( $params as $param ) {
				$result .= '<option value="' . $param . '">' . $param . '</option>';
			}
			return $result;
		}
		return '';
	}

	/**
	 * Return json encoded catalogs, or empty string
	 * @param $product_catalogs
	 *
	 * @return string
	 */
	public function return_catalogs_to_view( $product_catalogs )
	{
		if ($product_catalogs === null) {
			return 'ERR'; // Interpreted by salesmanagoRefreshCatalogList as Error Message
		} else {
			return json_encode($product_catalogs);
		}
	}

	/**
	 * @return string
	 */
	public function generateSwJs() {
		$serverSide = array(
			__( 'No permissions to write in the root directory', 'salesmanago' ) => is_writable(
				$_SERVER['DOCUMENT_ROOT']
			),
			__( 'Function \'file_put_contents\' is not available', 'salesmanago' ) => function_exists(
				'file_put_contents'
			),
			__( 'Function \'file_get_contents\' is not available', 'salesmanago' ) => function_exists(
				'file_get_contents'
			),
			__( 'Function \'file_exists\' is not available', 'salesmanago' ) => function_exists( 'file_exists' ),
		);

		try {
			foreach ( $serverSide as $key => $value ) {
				if ( $value === false ) {
					error_log( 'No permissions to write new file: ' . $key );
					return $key;
				}
			}

			$code = "importScripts('" . $this->Configuration->getEndpoint() . "/static/sm-sw.js');";

			if ( file_exists( $path = $_SERVER['DOCUMENT_ROOT'] . '/sw.js' ) ) {
				if ( md5( trim( file_get_contents( $path ) ) ) !== md5( trim( $code ) ) ) {
					error_log( 'INFO: sw.js file exists' );
					return __(
						'A different \'sw.js\' file already exists. Your developer should modify this file manually',
						'salesmanago'
					);
				}
				return __( 'File \'sw.js\' created correctly', 'salesmanago' );
			}

			file_put_contents( $path, $code );

			return file_exists( $path )
				? __( 'File \'sw.js\' created correctly', 'salesmanago' )
				: __(
					'Something went wrong while trying to create sw.js. Check server error log for more details',
					'salesmanago'
				);
		} catch ( \Exception $e ) {
			error_log( $e->getMessage(), $e->getCode() );
			return __(
				'Something went wrong while trying to create sw.js. Check server error log for more details',
				'salesmanago'
			);
		}
	}

	/**
	 * Get current day log from the file
	 *
	 * @param  bool  $is_api_v3
	 * @return false|string|void
	 */
	public static function getErrorLog( $is_api_v3 = false )
    {
		try {
			$logs         = '';
			$log_catalog = $is_api_v3 ? '/sm-logs/api-v3/' : '/sm-logs/';
			$sm_log_dir = wp_upload_dir( null, false )['basedir'] . $log_catalog;
			$error_log_path = $sm_log_dir . date('d-m-Y') . '.log';
			if ( is_readable( $error_log_path ) ) {
				$handler = fopen( $error_log_path, 'r' );
				if ( $handler ) {
					while ( ! feof( $handler ) ) {
						$line = fgets( $handler );
						$logs .= $line;
					}
				} else {
					return false;
				}
				fclose( $handler );
				return strip_tags($logs);
			}
			return false;
		} catch ( Error | \Exception $e ) {
			error_log("Unable to fetch log data");
		}
	}

	/**
	 * @return string
	 */
	public function getAboutInfo() {
		$result = '';
		try {
			$result .= 'SM version: ' . SM_VERSION . "\n";
		} catch ( \Exception $e ) {
		}
		try {
			global $wp_version;
			$result .= 'WP version: ' . $wp_version . "\n";
		} catch ( \Exception $e ) {
		}
		try {
			$plugin_data = get_plugin_data( ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php' );
			$result     .= 'WC version: ' . $plugin_data['Version'] . "\n";
		} catch ( \Exception $e ) {
		}
		try {
			$plugin_data = get_plugin_data( ABSPATH . 'wp-content/plugins/contact-form-7/wp-contact-form-7.php' );
			$result     .= 'CF7 version: ' . $plugin_data['Version'] . "\n";
		} catch ( \Exception $e ) {
		}
		try {
			$plugin_data = get_plugin_data( ABSPATH . 'wp-content/plugins/gravityforms/gravityforms.php' );
			$result     .= 'GF version: ' . $plugin_data['Version'] . "\n";
		} catch ( \Exception $e ) {
		}
		try {
			$result .= "________________________________\n";
			$result .= "Platform settings:\n" . print_r( $this->PlatformSettings->jsonSerialize(), true ) . "\n";

			$result .= "________________________________\n";
			$result .= "Configuration:\n" . print_r( $this->Configuration->jsonSerialize(), true ) . "\n";
		} catch ( \Exception $e ) {
		}
		try {
			$result .= "________________________________\n";
			$result .= "PHP Info: \n\n";
			$result .= 'PHP Version: ' . PHP_VERSION . "\n";
		} catch ( \Exception $e ) {
		}
		return $result;
	}

    /**
     * @param $param
     *
     * @return int
     */
    private static function validateContactCookieTtl( $param )
    {
        if ( preg_match( '/^\d+/', $param ) && $param >= 0 && $param <= 3652 ) {
            return $param;
        }
        return 3652;
    }

    /**
     * @param $param
     *
     * @return string
     */
    private static function validateLocation($param)
    {
        if ( preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]+$/', $param ) && strlen($param) > 2 && strlen($param) < 37 ) {
            return $param;
        }
        return Helper::getLocation();
    }

	/**
	 * @return array
	 */
	public function getAttributes() {
		$query = "SELECT
			ID as productId
			FROM {$this->db->posts}
			WHERE ( post_type = 'product' OR post_type = 'product_variation' );";

		$productIds = $this->db->get_results($query, ARRAY_A);
		$attributes = [];

		foreach ( $productIds as $productId ) {
			$product = wc_get_product( $productId[ 'productId' ] );
			$attributes += $product->get_attributes();
		}

		$attributesLabeled = [];

		foreach ($attributes as $attribute) {
			$attributesLabeled[] = [
				'name' => $attribute->get_name(),
				'label' => wc_attribute_label( $attribute->get_name() )
			];
		}

		return $attributesLabeled ?? [];
	}
}
