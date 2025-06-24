<?php

namespace bhr\Frontend\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Frontend\Controller\TransferController;
use bhr\Frontend\Model\Settings as SettingsModel;

use bhr\Frontend\Plugins\Wp\WpController;
use bhr\Frontend\Plugins\Wc\WcController;
use bhr\Frontend\Plugins\Cf7\Cf7Controller;
use bhr\Frontend\Plugins\Gf\GfController;
use bhr\Frontend\Plugins\Ff\FfController;
use Error;

class HooksModel {

	private $SettingsModel;

    public function __construct(SettingsModel $SettingsModel)
    {
        $this->SettingsModel = $SettingsModel;

        /* Monitoring code hook */
        if (!isset($this->SettingsModel->getPlatformSettings()->disableMonitoringCode)
        || !$this->SettingsModel->getPlatformSettings()->disableMonitoringCode) {
            Helper::addAction("wp_print_footer_scripts", array($this, "addMonitoringCode"));
        }

        /* WordPress hooks */
        if (!empty($this->SettingsModel->getPlatformSettings()->PluginWp->active)
            && $this->SettingsModel->getPlatformSettings()->PluginWp->active) {
            Helper::addAction('user_register', array($this, 'initWp'));
            Helper::addAction('wp_login', array($this, 'initWp'));
            if($this->SettingsModel->getPlatformSettings()->PluginWp->OptInInput->mode === 'append') {
                Helper::addAction('register_form',array($this, 'appendCheckbox'),14);
                Helper::addAction('woocommerce_register_form', array($this, 'appendCheckbox'),14);
            }
			if ($this->SettingsModel->getPlatformSettings()->PluginWp->OptInMobileInput->mode === 'append') {
				Helper::addAction('register_form',array($this, 'appendCheckboxMobile'),14);
				Helper::addAction('woocommerce_register_form', array($this, 'appendCheckboxMobile'),14);
			}
        }

        /* WooCommerce hooks */
        if (!empty($this->SettingsModel->getPlatformSettings()->PluginWc->active)
            && $this->SettingsModel->getPlatformSettings()->PluginWc->active) {
            /* Order Hooks */
            Helper::addAction('woocommerce_order_status_cancelled', array($this, 'initWc'), 10, 1);
            Helper::addAction('woocommerce_order_status_refunded', array($this, 'initWc'), 10, 1);
            // NOTE: purchase hook is set in PurchaseModel

            /* User Hooks */
            Helper::addAction('woocommerce_checkout_update_user_meta', array($this, 'initWc'));
            Helper::addAction('woocommerce_customer_save_address', array($this, 'initWc'));
            Helper::addAction('profile_update', array($this, 'initWc'), 10, 2);
            Helper::addAction('user_register', array($this, 'initWc'));
            Helper::addAction('wp_login', array($this, 'initWc'));

            /* Cart Hooks */
            Helper::addAction('woocommerce_add_to_cart', array($this, 'initWc'));
            Helper::addAction('woocommerce_update_cart_action_cart_updated', array($this, 'initWc'));
            Helper::addAction('woocommerce_cart_item_removed', array($this, 'initWc'));

            /* Purchase Hook */
            $purchaseHook = $this->SettingsModel->getPlatformSettings()->PluginWc->purchaseHook;
            if(!empty($purchaseHook)) {
                Helper::addAction($purchaseHook, array($this, 'initWc'));
            } else {
                Helper::addAction('woocommerce_order_status_changed', array($this, 'initWc'));
            }
            /* Opt-in input Hooks */
            if($this->SettingsModel->getPlatformSettings()->PluginWc->OptInInput->mode === 'append') {
                Helper::addAction('woocommerce_register_form', array($this, 'appendCheckbox'),14);
            } elseif($this->SettingsModel->getPlatformSettings()->PluginWc->OptInInput->mode === 'appendEverywhere') {
                Helper::addAction('woocommerce_register_form', array($this, 'appendCheckbox'),14);
                Helper::addAction('woocommerce_review_order_before_submit', array($this, 'appendCheckbox'),14);
            }
	        if ($this->SettingsModel->getPlatformSettings()->PluginWc->OptInMobileInput->mode === 'append') {
		        Helper::addAction('woocommerce_register_form', array($this, 'appendCheckboxMobile'),14);
	        } elseif($this->SettingsModel->getPlatformSettings()->PluginWc->OptInMobileInput->mode === 'appendEverywhere') {
		        Helper::addAction('woocommerce_register_form', array($this, 'appendCheckboxMobile'),14);
		        Helper::addAction('woocommerce_review_order_before_submit', array($this, 'appendCheckboxMobile'),14);
	        }
        }

        /* Contact Form 7 hook */
        if (!empty($this->SettingsModel->getPlatformSettings()->PluginCf7->active)
            && $this->SettingsModel->getPlatformSettings()->PluginCf7->active) {
            Helper::addAction('wpcf7_mail_sent', array($this, 'initCf7'), 5);
        }

        /* Gravity Forms hook */
        if (!empty($this->SettingsModel->getPlatformSettings()->PluginGf->active)
            && $this->SettingsModel->getPlatformSettings()->PluginGf->active) {
            Helper::addAction('gform_after_submission', array($this, 'initGf'), 10, 2);
        }

        /* Fluent Forms hook */
        if (!empty($this->SettingsModel->getPlatformSettings()->PluginFf->active)
            && $this->SettingsModel->getPlatformSettings()->PluginFf->active) {
            Helper::addAction('fluentform_before_insert_submission', array($this, 'initFf'), 10, 3);
        }

    }

    /**
     *
     */
    public function addMonitoringCode()
    {
        print MonitCodeModel::getMonitCode(
            $this->SettingsModel->getConfiguration()->getClientId(),
            $this->SettingsModel->getConfiguration()->getEndpoint(),
            $this->SettingsModel->getConfiguration()->getSmApp(),
            array(
                'disabled'  => $this->SettingsModel->getPlatformSettings()->MonitCode->disableMonitoringCode,
                'smcustom'  => $this->SettingsModel->getPlatformSettings()->MonitCode->smCustom,
                'smbanners' => $this->SettingsModel->getPlatformSettings()->MonitCode->smBanners,
                'popUpJs'   => $this->SettingsModel->getPlatformSettings()->MonitCode->popUpJs,

            )
        );
    }

    /**
     * @param $data
     */
    public function initWp($data)
    {
        $TransferController = new TransferController(
            $this->SettingsModel->getConfiguration(),
            $this->SettingsModel->getPlatformSettings()->PluginWp
        );
        if(!$WpController = new WpController($this->SettingsModel->getPlatformSettings(), $TransferController)) {
            return false;
        }

        $currentHook = Helper::currentFilter();
        switch ($currentHook) {
            case 'user_register':
                $WpController->registerUser($data);
                break;
            case 'wp_login':
                $WpController->loginUser($data);
                break;
        }
        return true;
    }

    /**
     * @param $data
     * @param $oldData
     *
     * @return bool
     */
    public function initWc($data, $oldData = null)
    {
        $TransferController = new TransferController(
            $this->SettingsModel->getConfiguration(),
            $this->SettingsModel->getPlatformSettings()->PluginWc
        );
        if(!$WcController = new WcController($this->SettingsModel->getPlatformSettings(), $this->SettingsModel->getConfiguration(), $TransferController)) {
            return false;
        }

        $currentHook = Helper::currentFilter();
        switch ($currentHook) {
            case 'woocommerce_order_status_cancelled':
            case 'woocommerce_order_status_refunded':
            case 'woocommerce_order_status_failed':
                $WcController->orderStatusChanged($data);
                break;
            case 'woocommerce_checkout_update_user_meta':
            case 'woocommerce_customer_save_address':
            case 'profile_update':
                $WcController->createUser($data, $oldData);
                break;
            case 'user_register':
                $WcController->registerUser();
                break;
            case 'wp_login':
                $WcController->loginUser($data);
                break;
            case 'woocommerce_add_to_cart':
            case 'woocommerce_update_cart_action_cart_updated':
            case 'woocommerce_cart_item_removed':
                $WcController->addToCart();
                break;
            case 'woocommerce_order_status_changed':
                $WcController->purchase($data);
                break;
            default:
                $WcController->purchase($data);
                break;
        }
        return true;
    }

	/**
	 * @param $data
	 */
	public function initCf7( $data ) {
		try {
			$TransferController = new TransferController(
				$this->SettingsModel->getConfiguration(),
				$this->SettingsModel->getPlatformSettings()->PluginCf7
			);
			if ( ! $Cf7Controller = new Cf7Controller( $this->SettingsModel->getPlatformSettings(), $TransferController ) ) {
				return false;
			}
			$contact_id = $Cf7Controller->execute( $data );
			Helper::send_rgif_request( Helper::build_rgif_url( $contact_id ) );
			return true;
		} catch ( Error | \Exception $e ) {
			error_log( $e->getMessage() );
			return false;
		}
	}

    /**
     * @param $formValues
     * @param $formSetup
     */
    public function initGf($formValues, $formSetup)
    {
        $TransferController = new TransferController(
            $this->SettingsModel->getConfiguration(),
            $this->SettingsModel->getPlatformSettings()->PluginGf
        );
        if(!$GfController = new GfController($this->SettingsModel->getPlatformSettings(), $TransferController)) {
            return false;
        }
        $GfController->execute($formValues, $formSetup);
        return true;
    }

    /**
     * @param $entryId
     * @param $formData
     * @param $form - unused
     * @return bool
     */
    public function initFf($entryId, $formData, $form)
    {
        $TransferController = new TransferController(
            $this->SettingsModel->getConfiguration(),
            $this->SettingsModel->getPlatformSettings()->PluginFf
        );
        if(!$FfController = new FfController($this->SettingsModel->getPlatformSettings(), $TransferController)) {
            return false;
        }
        $FfController->execute($formData, $entryId, $form);
        return true;
    }

    /**
     *
     */
    public function appendCheckbox()
    {
        $cssClass = 'sm-opt-in-input';
        $inputName = 'sm-optin-email';
        $inputId = 'sm-optin-email-checkbox';

        $label = ($this->SettingsModel->getPlatformSettings()->PluginWc->active)
            ? $this->SettingsModel->getPlatformSettings()->PluginWc->OptInInput->label
            : $this->SettingsModel->getPlatformSettings()->PluginWp->OptInInput->label;

        Helper::loadPluginTextDomain('salesmanago', false, 'salesmanago/languages');

        //Get translation for input label. If there is no translation or using default language, use label declared in admin settings.
        $displayLabel = (__('!optInInputLabel', 'salesmanago') === '!optInInputLabel')
            ? $label
            : __('!optInInputLabel', 'salesmanago');

        echo '<p class="woocommerce-FormRow form-row ' . $cssClass . '">
                <label class="woocommerce-form__label ' . $cssClass . '" for="' . $inputId . '" style="margin-left: 0; display: flex; align-items: center;">
                    <input id="' . $inputId . '"
                        name="' . $inputName . '"
                        class="' . $cssClass . '"
                        type="checkbox"
                        style="margin-right: 8px;"
                        aria-required="false">
                    <span class="' . $cssClass . '">' . $displayLabel . '</span>
                </label>
            </p>';
    }

    /**
     *
     */
    public function appendCheckboxMobile()
    {
        $cssClass = 'sm-opt-in-input';
        $inputName = 'sm-optin-mobile';
        $inputId = 'sm-optin-moblie-checkbox';

        $label = ($this->SettingsModel->getPlatformSettings()->PluginWc->active)
            ? $this->SettingsModel->getPlatformSettings()->PluginWc->OptInMobileInput->label
            : $this->SettingsModel->getPlatformSettings()->PluginWp->OptInMobileInput->label;

        Helper::loadPluginTextDomain('salesmanago', false, 'salesmanago/languages');

        //Get translation for input label. If there is no translation or using default language, use label declared in admin settings.
        $displayLabel = (__('!optInMobileInputLabel', 'salesmanago') === '!optInMobileInputLabel')
            ? $label
            : __('!optInMobileInputLabel', 'salesmanago');

        echo '<p class="woocommerce-FormRow form-row ' . $cssClass . '">
                <label class="woocommerce-form__label ' . $cssClass . '" for="' . $inputId . '" style="margin-left: 0; display: flex; align-items: center;">
                    <input id="' . $inputId . '"
                        name="' . $inputName . '"
                        class="' . $cssClass . '"
                        type="checkbox"
                        style="margin-right: 8px;"
                        aria-required="false">
                    <span class="' . $cssClass . '">' . $displayLabel . '</span>
                </label>
            </p>';
    }
}
