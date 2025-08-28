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
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Report\ReportModel;
use SALESmanago\Services\Report\ReportService;
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

		/* PRODUCT CATALOG PAGE */
		elseif ( $page == 'salesmanago-product-catalog' ) {
			$this->getPlatformSettings()
				->setCronEnabled( $request['cronEnabled'] ?? false )
				->setCronMethod( $request['cronMethod'] ?? 'real-time' )
				->setCronValue( $request['cronValue'] ?? '0' );
		}

        /* LEADOO SETTINGS PAGE */
        elseif ($page == 'leadoo' ) {
            $leadooScriptFromConfig = ($this->getConfiguration()->getConfiguration())
                ? $this->getConfiguration()->getLeadooScript()
                : '';
            $leadooScriptFromView = $request['leadoo_script'] ?? '';

            $isEmptyLeadooScriptFromConfig = empty($leadooScriptFromConfig);
            $isEmptyLeadooScriptFromView = empty($leadooScriptFromView);

            if ($isEmptyLeadooScriptFromConfig && !$isEmptyLeadooScriptFromView) {
                $this->report(ReportModel::ACT_LOGIN);
            }

            if (!$isEmptyLeadooScriptFromConfig && $isEmptyLeadooScriptFromView) {
                $this->report(ReportModel::ACT_LOGOUT);
            }

            $this->getConfiguration()
                ->setLeadooScript($leadooScriptFromView);
        }

		return $this;
	}

    /**
     * Report user action to SALESmanago reporting service via reporting 2.0
     *
     * @param string $act - action type
     */
    public function report(string $act = ReportModel::ACT_UNKNOWN)
    {
        try {
            $conf = UnionConfigurationEntity::getInstance();

            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/salesmanago/salesmanago.php' );
            //this mark event as leadoo integration in location:
            $version = 'wordpress_leadoo_' . $plugin_data['Version'];

            $ownerFromConfig = $this->getConfiguration()->getOwner();
            $email = $this->generateEmailFromSiteUrl();

            if (!empty($ownerFromConfig)) {
                $email = $ownerFromConfig;
            }

            $conf
                ->setActiveReporting(true)
                ->setPlatformName('WORDPRESS_LEADOO')
                ->setEndpoint('salesmanago.com')
                ->setOwner( $email )
                ->setPlatformVersion(get_bloginfo('version'))
                ->setVersionOfIntegration($version)
                ->setPlatformDomain(get_site_url());

                ReportService::getInstance($conf)->reportAction($act);
        } catch ( \Exception | Error $e ) {
            error_log( $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine() );
        }
    }

    /**
     * Generate email address based on site URL
     * used for leadoo reporting
     *
     * @return string
     */
    public function generateEmailFromSiteUrl()
    {
        try {
            $siteUrl = get_site_url();

            $host = parse_url($siteUrl, PHP_URL_HOST);

            if (!$host) {
                $host = preg_replace('#^https?://#', '', $siteUrl);
                $host = preg_replace('#[:/].*$#', '', $host);
            }

            $host = preg_replace('/[^a-zA-Z0-9\-\.]/', '.', $host);
            $host = preg_replace('/\.{2,}/', '.', $host);
            $host = trim($host, '.');
            return $host. '@noreply-salesmanago.com';
        } catch (\Exception $e) {
            error_log( $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine() );
            return 'noreply@salesmanago.com';
        }
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
            ->setLeadooScript( isset( $conf->leadooScript ) ? $conf->leadooScript : '' )
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

		$PlatformSettings->setDetailsMapping( $settings->DetailsMapping ?? Helper::generateDefaultMapping() );
		$PlatformSettings->setCronEnabled( (bool) ($settings->cronEnabled ?? false) );
		$PlatformSettings->setCronValue( $settings->cronValue ?? '0' );
		$PlatformSettings->setCronMethod( $settings->cronMethod ?? 'real-time' );
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

		$platformSettings->DetailsMapping = Helper::generateDefaultMapping();

		$platformSettings->cronEnabled = false;
		$platformSettings->cronMethod = 'real-time';
		$platformSettings->cronValue = '0';

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
		return 'data:image/svg+xml;base64,' . 'iVBORw0KGgoAAAANSUhEUgAAACoAAAAkCAYAAAD/yagrAAACSUlEQVR4Xu2WvUoDQRSF8yh5ADsb7WzURgvRxkLQJqCFjWCTQjFNhKCVQoiVhdglqKWyWiqKdqJ1kgfIC4x7ZnM316MSvczKIn4wZDN7uefs/NyZgnOuGLfIhecxbsVCKPoJsyJiPTOcOTSsZ4YTh4b1zHDi0LCeGU78EzrdLnd9gPXMcOLvslvbd+XtHe7+AOuZ4cTD6PV6/ndkdCy/Ro9PTt36xqZ/zqXRTidZi1Mzc26ltOafc2f07v7BjU9M+udcGy1v7XhT4E8bDUDT4c7AvUwOjILoW0arcc0E84tLfueDqdk59/zyqkMzZajRdn/HA6mh/PwbDDWaF/6NhiZTo1jHzbMLd1BvuNb5xZe3LcRhkyLuKrrm157MjEIQJxrKmG6H9aM0BgZxC+OY6bii8EdlYhR3AxGF2eXSqq/BA7MNb3IhLnfSh/eI02Z1ZYHRKP0XCBFErdVimFr0tePR0ialNgOUQ5kJPfowWozbU9oTABHChQaHAqZRDMMIDItJrF28Q4w0HDB4J1dKwEe/mTSjS45XtMvoxhu9jQ3LJsHvYAkkIwbziJMmHyLHNWA9M2lGN5h6GNXoDYYYrGWY4Y0jIypHN2A9M0rHT3kyYg0/fRghGJKRlHWq+8rbFW8M8eWtiv8gxAisZ0b59Oh1qBtGUq9XXQ2kwSQ+VsN6Zt5l7QMj1dqeHyX8sriAQwExMvqfXXhYzwwnDg3rmeHEoWE9M5w4NKxnhhOHhvXMcOLQsJ4Zl8GdQdFiPTMuuTO0WCEAOHuLb4s1mZAG4ffuAAAAAElFTkSuQmCC';
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

    /**
     * @return CronController
     */
	public function getCronController() {
		return $this->CronController;
	}
}
