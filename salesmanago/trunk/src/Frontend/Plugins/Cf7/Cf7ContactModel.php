<?php

namespace bhr\Frontend\Plugins\Cf7;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Frontend\Model\AbstractContactModel;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Exception\Exception;
use stdClass;

class Cf7ContactModel extends AbstractContactModel {

	private $currentFormConfig;

	public function __construct( $PlatformSettings ) {
		// do not continue without settings
		if ( empty( $PlatformSettings ) || empty( $PlatformSettings->PluginCf7 ) ) {
			return false;
		}
		// create an Abstract Contact
		parent::__construct( $PlatformSettings, $PlatformSettings->PluginCf7 );
		return true;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function setCurrentFormConfig( $id ) {
		// Get config (tags, owner) for submitted form
		$id = (int) $id;
		if ( isset( $this->PluginSettings->forms->$id ) ) {
			$this->currentFormConfig = $this->PluginSettings->forms->$id;
			return true;
		}
		return false;
	}

	/**
	 * @return mixed
	 */
	public function getCurrentFormConfig() {
		return $this->currentFormConfig;
	}

	/**
	 * @return Contact|null
	 * @throws Exception
	 */
	public function parseContact( $formData ) {
		try {
			if ( ! isset( $formData['sm-email'] ) || empty( $formData['sm-email'] ) ) {
				return null;
			}

			/* Contact */
			$this->Contact
				->setEmail( $formData['sm-email'] )
				->setName( isset( $formData['sm-name'] ) ? $formData['sm-name'] : '' )
				->setPhone( isset( $formData['sm-phone'] ) ? $formData['sm-phone'] : '' )
				->setFax( isset( $formData['sm-fax'] ) ? $formData['sm-fax'] : '' )
				->setCompany( isset( $formData['sm-company'] ) ? $formData['sm-company'] : '' );

			/* Birthday */
			if ( ! empty( $formData['sm-birthday'] ) ) {
				$bd_timestamp = strtotime( $formData['sm-birthday'] );
				$this->Contact->setBirthday( $bd_timestamp ?? '' );
			}

			/* Address */
			$this->Address
				->setCity( isset( $formData['sm-city'] ) ? $formData['sm-city'] : '' )
				->setCountry( isset( $formData['sm-country'] ) ? $formData['sm-country'] : '' )
				->setStreetAddress( isset( $formData['sm-address'] ) ? $formData['sm-address'] : '' )
				->setZipCode( isset( $formData['sm-postcode'] ) ? $formData['sm-postcode'] : '' );

			/* Options */
			$this->setLanguage();
			$this->Options
				->setTags(
					isset( $this->currentFormConfig->tags )
						? $this->currentFormConfig->tags
						: ''
				)->setRemoveTags(
					isset( $this->currentFormConfig->tagsToRemove )
						? $this->currentFormConfig->tagsToRemove
						: ''
				);

			/* Global optin status (for both, email and mobile marketing) */
			$optIn = isset( $formData['sm-optin'] )
				&& ( is_array( $formData['sm-optin'] ) && implode( $formData['sm-optin'] ) != '' )
				|| ( is_string( $formData['sm-optin'] ) && boolval( $formData['sm-optin'] ) );

			/* Email marketing opt in status */
			$optInEmail = isset( $formData['sm-optin-email'] )
				&& ( ( is_array( $formData['sm-optin-email'] ) && implode( $formData['sm-optin-email'] ) != '' )
				|| ( is_string( $formData['sm-optin-email'] ) && boolval( $formData['sm-optin-email'] ) ) );

			/* Mobile marketing opt in status */
			$optInMobile = isset( $formData['sm-optin-mobile'] )
				&& ( ( is_array( $formData['sm-optin-mobile'] ) && implode( $formData['sm-optin-mobile'] ) != '' )
				|| ( is_string( $formData['sm-optin-mobile'] ) && boolval( $formData['sm-optin-mobile'] ) ) );

			if ( $optIn || $optInEmail ) {
				$this->Options->setIsSubscribesNewsletter( true );
				$this->Options->setIsSubscriptionStatusNoChange( false ); // Set flag - opt-in status has changed
			}

			if ( $optIn || $optInMobile ) {
				$this->Options->setIsSubscribesMobile( true );
				$this->Options->setIsSubscriptionStatusNoChange( false ); // Set flag - opt-in status has changed
			}

			/* Custom properties */
			$propertiesMap         = $this->getPropertiesMap( $formData );
			$propertiesMappingMode = ! empty( $this->PlatformSettings->PluginCf7->propertiesMappingMode )
				? $this->PlatformSettings->PluginCf7->propertiesMappingMode
				: 'details';

			$this->setPropertiesAsMappedType( $propertiesMappingMode, $propertiesMap );

			return $this->Contact;
		} catch ( \Exception $e ) {
			throw new Exception( $e->getMessage() );
		}
	}

	/**
	 * Get custom DOI settings per form
	 * As of 3.2.3 DOI no longer requires account and subject, only EMAIL ID
	 *
	 * @param $formData
	 *
	 * @return false|stdClass
	 */
	public function getCustomDoubleOptIn( $formData ) {
		/* per-form Double Opt-In templates */
		if ( ! empty( $formData['sm-doi-email-id'] ) ||
			 ( ! empty( $formData['sm-doi-template-id'] )
			   && ! empty( $formData['sm-doi-account-id'] )
			   && ! empty( $formData['sm-doi-subject'] ) ) ) {
			$PlatformSettings    = new stdClass();
			$DoubleOptIn         = new stdClass();
			$DoubleOptIn->active = true;

			if ( ! empty( $formData['sm-doi-email-id'] ) ) {
				$DoubleOptIn->emailId = $formData['sm-doi-email-id'];
			} else {
				$DoubleOptIn->templateId = $formData['sm-doi-template-id'];
				$DoubleOptIn->accountId  = $formData['sm-doi-account-id'];
				$DoubleOptIn->subject    = $formData['sm-doi-subject'];
			}
			$PlatformSettings->DoubleOptIn = $DoubleOptIn;
			return $PlatformSettings;
		}
		return false;
	}


	/**
	 * @param $formData
	 * @return array
	 */
	private function getPropertiesMap( $formData ) {
		$properties = array();
		if ( isset( $this->PluginSettings->properties ) ) {
			foreach ( $this->PluginSettings->properties as $propertyName ) {
				$customInput = isset( $formData[ $propertyName ] )
					? $formData[ $propertyName ]
					: '';

				if ( ! empty( $propertyName ) && ! empty( $customInput ) ) {
					$properties[ $propertyName ] = $customInput;
				}
			}
		}
		return $properties;
	}

	/**
	 * Filter empty data from form data
	 * Converts arrays elements from form data into string
	 *
	 * @param array $formData
	 *
	 * @return array
	 */
	public function filterFormData( array $formData ) {
		$formData = array_filter( $formData );

		foreach ( $formData as $key => $value ) {
			if ( is_array( $value ) ) {
				$formData[ $key ] = implode( ',', $value );
			}
		}

		return $formData;
	}
}
