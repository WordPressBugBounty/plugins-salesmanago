<?php

namespace bhr\Frontend\Plugins\Gf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Frontend\Model\AbstractContactModel;
use bhr\Frontend\Model\Helper;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Exception\Exception;
use stdClass;

class GfContactModel extends AbstractContactModel {

	const
		CONF          = 'confirmation',
		CONF_DOUBLE   = 'double',
		PROPS         = 'properties',
		PREFIX_FIELD  = 'sm-',
		CONSENT_FIELD = 'sm-consent-',

		FIELDS          = 'fields',
		NAME_FIELD      = 'name',
		LAST_NAME_FIELD = 'lastname',
		EMAIL_FIELD     = 'email',
		PHONE_FIELD     = 'phone',
		COMPANY_FIELD   = 'company',
		BIRTHDAY_FIELD  = 'birthday',
		ADDRESS_FIELD   = 'address',
		ZIPCODE_FIELD   = 'zipcode',
		CITY_FIELD      = 'city',
		COUNTRY_FIELD   = 'country',
		OPT_IN_FIELD    = 'optin',

		FORCE_OPTIN = 'forceOptIn',

		CONTACT_CONSENTS = 'consents',

		PROVINCE_FIELD = 'province';

	protected $currentFormConfig;
	protected $formFieldsValues = array();

	public function __construct( $PlatformSettings ) {
		// do not continue without settings
		if ( empty( $PlatformSettings ) || empty( $PlatformSettings->PluginGf ) ) {
			return false;
		}
		// create an Abstract Contact
		parent::__construct( $PlatformSettings, $PlatformSettings->PluginGf );
		return true;
	}

	/**
	 * @param $form
	 * @return bool
	 */
	public function setCurrentFormConfig( $form ) {
		// Get config (tags, owner) for submitted form
		$id = (int) $form['id'];
		if ( isset( $this->PluginSettings->forms->$id ) ) {
			$this->currentFormConfig = $this->PluginSettings->forms->$id;
			return true;
		}
		return false;
	}

	/**
	 * @param $formValues
	 * @param $formSetup
	 * @return Contact|null
	 * @throws Exception
	 */
	public function parseContact( $formValues, $formSetup ) {
		try {
			// Translate Form Values and Form Setup into a single associative array
			$this->formFieldsValues = $this->translateFieldsAndValues( $formValues, $formSetup );

			if ( ! isset( $this->formFieldsValues['email'] )
				|| empty( $this->formFieldsValues['email'] ) ) {
				return null;
			}

			/* Contact */
			$this->Contact
				->setEmail( $this->formFieldsValues['email'] )
				->setName(
					isset( $this->formFieldsValues['name'] )
						? trim( implode( ' ', $this->formFieldsValues['name'] ) )
						: ''
				)
				->setPhone(
					isset( $this->formFieldsValues[ self::PHONE_FIELD ] )
					? $this->formFieldsValues[ self::PHONE_FIELD ]
					: ''
				)
				->setCompany(
					isset( $this->formFieldsValues[ self::COMPANY_FIELD ] )
					? $this->formFieldsValues[ self::COMPANY_FIELD ]
					: ''
				);

			if ( ! empty( $this->formFieldsValues[ self::BIRTHDAY_FIELD ] ) ) {
				$bd_timestamp = strtotime( $this->formFieldsValues[ self::BIRTHDAY_FIELD ] );
				$this->Contact->setBirthday( $bd_timestamp ?? '' );
			}

			/* Address */
			$this->Address
				->setStreetAddress(
					isset( $this->formFieldsValues[ self::ADDRESS_FIELD ] )
					? trim( implode( ' ', $this->formFieldsValues[ self::ADDRESS_FIELD ] ) )
					: ''
				)
				->setCountry(
					isset( $this->formFieldsValues[ self::COUNTRY_FIELD ] )
					? $this->formFieldsValues[ self::COUNTRY_FIELD ]
					: ''
				)
				->setCity(
					isset( $this->formFieldsValues[ self::CITY_FIELD ] )
					? $this->formFieldsValues[ self::CITY_FIELD ]
					: ''
				)
				->setZipCode(
					isset( $this->formFieldsValues[ self::ZIPCODE_FIELD ] )
					? $this->formFieldsValues[ self::ZIPCODE_FIELD ]
					: ''
				)
				->setProvince(
					isset( $this->formFieldsValues[ self::PROVINCE_FIELD ] )
					? $this->formFieldsValues[ self::PROVINCE_FIELD ]
					: ''
				);

			/* Options */
			$this->setLanguage();
			$this->Options
				->setTags(
					isset( $this->currentFormConfig->tags )
						? $this->currentFormConfig->tags
						: ''
				)
				->setRemoveTags(
					isset( $this->currentFormConfig->tagsToRemove )
						? $this->currentFormConfig->tagsToRemove
						: ''
				);

			/* Global optin status (for both, email and mobile marketing) */
			$optIn = ! empty( $this->formFieldsValues['opt-in'] ) && boolval( $this->formFieldsValues['opt-in'] );

			/* Email marketing opt in status */
			$optInEmail = ! empty( $this->formFieldsValues['opt-in-email'] ) && boolval( $this->formFieldsValues['opt-in-email'] );

			/* Mobile marketing opt in status */
			$optInMobile = ! empty( $this->formFieldsValues['opt-in-mobile'] ) && boolval( $this->formFieldsValues['opt-in-mobile'] );

			if ( $optIn || $optInEmail ) {
				$this->Options->setIsSubscribesNewsletter( true );
				$this->Options->setIsSubscriptionStatusNoChange( false );
			}

			if ( $optIn || $optInMobile ) {
				$this->Options->setIsSubscribesMobile( true );
				$this->Options->setIsSubscriptionStatusNoChange( false );
			}

			/* Properties */
			$propertiesMappingMode = ! empty( $this->PlatformSettings->PluginGf->propertiesMappingMode )
				? $this->PlatformSettings->PluginGf->propertiesMappingMode
				: 'details';

			$this->setPropertiesAsMappedType(
				$propertiesMappingMode,
				isset( $this->formFieldsValues['properties'] )
					? $this->formFieldsValues['properties']
					: array()
			);

			return $this->Contact;
		} catch ( \Exception $e ) {
			throw new Exception( $e->getMessage() );
		}
	}

	/**
	 * Get custom DOI settings per form
	 * As of 3.2.3 DOI no longer requires account and subject, only EMAIL ID
	 *
	 * @return false|stdClass
	 */
	public function getCustomDoubleOptIn() {
		if ( empty( $this->formFieldsValues ) ) {
			return false;
		}
		/* per-form Double Opt-In templates */
		if ( ! empty( $formData['sm-doi-email-id'] ) ||
			 ( ! empty( $formData['sm-doi-template-id'] )
			   && ! empty( $formData['sm-doi-account-id'] )
			   && ! empty( $formData['sm-doi-subject'] ) ) ) {
			$PlatformSettings = new stdClass();
			$DoubleOptIn      = new stdClass();

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
	 * @param $formValues
	 * @param $formSetup
	 * @return array
	 */
	private function translateFieldsAndValues( $formValues, $formSetup ) {
		$output = array();
		foreach ( $formSetup['fields'] as $field ) {
			// If field has no name or id
			if ( empty( $field->adminLabel ) || empty( $field->id ) ) {
				if ( ! empty( $field->label ) ) {
					$output[ $field->label ] = Helper::getGfFieldValue( $formValues, (string) $field->id );
				}
				continue;
			}

			// Get field's value
			$value = Helper::getGfFieldValue( $formValues, (string) $field->id );

			// Check if field is a custom property
			if ( ! empty( $this->PluginSettings->properties )
			&& in_array( $field->adminLabel, $this->PluginSettings->properties ) ) {
				// get array of inputs. inputs are arrays (id, label, name)
				$inputs = $field->get_entry_inputs();

				if ( is_array( $inputs ) ) {

					// tmp array to hold values of input array. Workaround for array_column incompatibility
					$inputValues = array();

					foreach ( $inputs as $input ) {

						// magic happens and we get the label
						$value = Helper::getGfFieldValue( $formValues, (string) $input['id'] );
						if ( ! empty( $value ) ) {
							$inputValues[] = $value;
						}
					}
					$output['properties'][ $field->adminLabel ]
						= mb_substr( implode( ',', $inputValues ), 0, self::MAX_STRING_LENGTH );
				} else {
					$output['properties'][ $field->adminLabel ] = mb_substr( $value, 0, self::MAX_STRING_LENGTH );
				}
				continue;
			}

			// Basic fields should start with 'sm-'
			if ( strpos( $field->adminLabel, self::PREFIX_FIELD ) === false
			&& strpos( $field->adminLabel, 'sm_' ) === false ) {
				continue;
			}

			// Remove 'sm-' from field's name
			$key = strtolower( substr( $field->adminLabel, 3 ) );

			// Correct for common mistakes
			switch ( $key ) {
				case 'name':
				case 'firstname':
				case 'firstName':
				case 'lastName':
				case 'last_name':
				case 'lastname':
				case 'first_name':
					$output['name'][] = $value;
					break;
				case 'e-mail':
					$output['email'] = $value;
					break;
				case 'address':
				case 'streetaddress':
				case 'streetAddress':
				case 'street-address':
				case 'address1':
				case 'address-1':
				case 'address_1':
				case 'streetaddress2':
				case 'streetAddress2':
				case 'street-address-2':
				case 'address2':
				case 'address-2':
				case 'address_2':
					$output['address'][] = $value;
					break;
				case 'optin':
				case 'opt-in':
					$output['opt-in'] = empty( $value )
						? Helper::getGfFieldValue( $formValues, (string) $field->get_entry_inputs()[0]['id'] )
						: $value;
					break;
				case 'optin-mobile':
				case 'opt-in-mobile':
				case 'optin-phone':
				case 'opt-in-phone':
					$output['opt-in-mobile'] = empty( $value )
						? Helper::getGfFieldValue( $formValues, (string) $field->get_entry_inputs()[0]['id'] )
						: $value;
					break;
				case 'optin-email':
				case 'opt-in-email':
				case 'optin-newsletter':
				case 'opt-in-newsletter':
					$output['opt-in-email'] = empty( $value )
						? Helper::getGfFieldValue( $formValues, (string) $field->get_entry_inputs()[0]['id'] )
						: $value;
					break;
				// Set field's value to output array
				default:
					$output[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
					break;
			}
		}
		return $output;
	}
}
