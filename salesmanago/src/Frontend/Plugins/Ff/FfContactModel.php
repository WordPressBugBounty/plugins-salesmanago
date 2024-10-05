<?php

namespace bhr\Frontend\Plugins\Ff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Frontend\Model\AbstractContactModel;
use DateTime;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Exception\Exception;
use stdClass;

class FfContactModel extends AbstractContactModel {

	private $currentFormConfig;

	public function __construct( $PlatformSettings ) {
		// do not continue without settings
		if ( empty( $PlatformSettings ) || empty( $PlatformSettings->PluginFf ) ) {
			return false;
		}
		// create an Abstract Contact
		parent::__construct( $PlatformSettings, $PlatformSettings->PluginFf );
		return true;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function setCurrentFormConfig( $id )
    {
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
	public function getCurrentFormConfig()
    {
		return $this->currentFormConfig;
	}

	/**
	 * Parse Contact from a Fluent Form.
	 *
	 * @param $form_data array Array of form data
	 * @param $date_format string Date format of the birthday date input
	 *
	 * @return Contact|null
	 * @throws Exception
	 */
	public function parseContact( $form_data, $date_format )
    {
		try {
			if ( empty( $form_data['sm_email'] ) ) {
				return null;
			}

			/* Contact */
			/* Fluent Forms name field can be an array or string */
			$name = '';
			if ( isset( $form_data['sm_name'] ) ) {
				$name = is_array( $form_data['sm_name'] )
					? implode( ' ', $form_data['sm_name'] )
					: $form_data['sm_name'];
			}

			$this->Contact
				->setEmail( $form_data['sm_email'] )
				->setName( $name )
				->setPhone( isset( $form_data['sm_phone'] ) ? $form_data['sm_phone'] : '' )
				->setFax( isset( $form_data['sm_fax'] ) ? $form_data['sm_fax'] : '' )
				->setCompany( isset( $form_data['sm_company'] ) ? $form_data['sm_company'] : '' );

			/* Birthday */
			if ( ! empty( $form_data['sm_birthday'] ) && $date_format ) {
				$dateTime = DateTime::createFromFormat( $date_format, $form_data['sm_birthday'] );
				$bd_timestamp = $dateTime ? $dateTime->getTimestamp() : '';
				$this->Contact->setBirthday( $bd_timestamp ?? '' );
			}

			/* Address */
			if ( isset( $form_data['sm_address'] ) && is_array( $form_data['sm_address'] ) ) {
				/* If somebody uses Fluent Forms' built-in address field */
				$streetAddress = $this->implodeFields( $form_data['sm_address']['address_line_1'], $form_data['sm_address']['address_line_2'] );

				$this->Address
					->setCity( isset( $form_data['sm_address']['city'] ) ? $form_data['sm_address']['city'] : '' )
					->setCountry( isset( $form_data['sm_address']['country'] ) ? $form_data['sm_address']['country'] : '' )
					->setZipCode( isset( $form_data['sm_address']['zip'] ) ? $form_data['sm_address']['zip'] : '' )
					->setProvince( isset( $form_data['sm_address']['state'] ) ? $form_data['sm_address']['state'] : '' )
					->setStreetAddress( $streetAddress );
			} else {
				/* If somebody creates their own address field */
				$streetAddress = $this->implodeFields( $form_data['sm_address1'], $form_data['sm_address2'] );

				$this->Address
					->setCity( isset( $form_data['sm_city'] ) ? $form_data['sm_city'] : '' )
					->setCountry( isset( $form_data['sm_country'] ) ? $form_data['sm_country'] : '' )
					->setZipCode( isset( $form_data['sm_postcode'] ) ? $form_data['sm_postcode'] : '' )
					->setProvince( isset( $form_data['sm_province'] ) ? $form_data['sm_province'] : '' )
					->setStreetAddress( $streetAddress );
			}
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
			$optIn = isset( $form_data['sm_optin'] )
				&& ( is_array( $form_data['sm_optin'] ) && implode( $form_data['sm_optin'] ) != '' )
				|| ( is_string( $form_data['sm_optin'] ) && boolval( $form_data['sm_optin'] ) );

			/* Email marketing opt in status */
			$optInEmail = isset( $form_data['sm_optin_email'] )
				&& ( ( is_array( $form_data['sm_optin_email'] ) && implode( $form_data['sm_optin_email'] ) != '' )
				|| ( is_string( $form_data['sm_optin_email'] ) && boolval( $form_data['sm_optin_email'] ) ) );

			/* Mobile marketing opt in status */
			$optInMobile = isset( $form_data['sm_optin_mobile'] )
				&& ( ( is_array( $form_data['sm_optin_mobile'] ) && implode( $form_data['sm_optin_mobile'] ) != '' )
				|| ( is_string( $form_data['sm_optin_mobile'] ) && boolval( $form_data['sm_optin_mobile'] ) ) );

			if ( $optIn || $optInEmail ) {
				$this->Options->setIsSubscribesNewsletter( true );
				$this->Options->setIsSubscriptionStatusNoChange( false ); // Set flag - opt-in status has changed
			}

			if ( $optIn || $optInMobile ) {
				$this->Options->setIsSubscribesMobile( true );
				$this->Options->setIsSubscriptionStatusNoChange( false ); // Set flag - opt-in status has changed
			}

			/* Custom properties */
			$propertiesMap         = $this->getPropertiesMap( $form_data );
			$propertiesMappingMode = ! empty( $this->PlatformSettings->PluginFF->propertiesMappingMode )
				? $this->PlatformSettings->PluginFF->propertiesMappingMode
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
		if ( ! empty( $formData['sm_doi_email_id'] ) ||
			( ! empty( $formData['sm_doi_template_id'] )
			  && ! empty( $formData['sm_doi_account_id'] )
			  && ! empty( $formData['sm_doi_subject'] ) ) ) {
			$PlatformSettings = new stdClass();
			$DoubleOptIn      = new stdClass();
			$DoubleOptIn->active = true;

			if ( ! empty( $formData['sm_doi_email_id'] ) ) {
				$DoubleOptIn->emailId = $formData['sm_doi_email_id'];
			} else {
				$DoubleOptIn->templateId = $formData['sm_doi_template_id'];
				$DoubleOptIn->accountId  = $formData['sm_doi_account_id'];
				$DoubleOptIn->subject    = $formData['sm_doi_subject'];
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

				if ( $propertyName != '' && $customInput != '' ) {
					$properties[ $propertyName ] = $customInput;
				}
			}
		}
		return $properties;
	}

	/**
	 * @param string $first
	 * @param string $second
	 * @return string
	 */
	private function implodeFields( $first = '', $second = '' ) {
		return trim( $first . ' ' . $second );
	}
}
