<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\AbstractEntity;

class Wc extends AbstractPlugin
{
    const
        DEFAULT_PURCHASE_HOOK           = 'woocommerce_order_status_changed',
        DEFAULT_PRODUCT_IDENTIFIER_TYPE = 'id',
        DEFAULT_LANGUAGE_DETECTION      = 'platform';

    protected $productIdentifierType   = self::DEFAULT_PRODUCT_IDENTIFIER_TYPE;
    protected $purchaseHook            = self::DEFAULT_PURCHASE_HOOK;
    protected $preventEventDuplication = false;

    public function setPluginSettings($pluginSettings)
    {
        parent::setPluginSettings($pluginSettings);

        $this->setProductIdentifierType(isset($pluginSettings->productIdentifierType)
            ? $pluginSettings->productIdentifierType
            : self::DEFAULT_PRODUCT_IDENTIFIER_TYPE);

        $this->setPurchaseHook(isset($pluginSettings->purchaseHook)
            ? $pluginSettings->purchaseHook
            : self::DEFAULT_PURCHASE_HOOK);

        $this->setPreventEventDuplication(isset($pluginSettings->preventEventDuplication)
            ? $pluginSettings->preventEventDuplication
            : false);

        return $this;
    }

    /**
     * @return string
     */
    public function getPurchaseHook()
    {
        return $this->purchaseHook;
    }

    /**
     * @param string $purchaseHook
     */
    public function setPurchaseHook($purchaseHook)
    {
        if (!empty($purchaseHook)) {
            $this->purchaseHook = $purchaseHook;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isPreventEventDuplication()
    {
        return $this->preventEventDuplication;
    }

    /**
     * @param bool $preventEventDuplication
     */
    public function setPreventEventDuplication($preventEventDuplication)
    {
        $this->preventEventDuplication = $preventEventDuplication;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductIdentifierType()
    {
        return $this->productIdentifierType;
    }

    /**
     * @param $productIdentifierType
     * @return $this
     */
    public function setProductIdentifierType($productIdentifierType)
    {
        $this->productIdentifierType = $productIdentifierType;
        return $this;
    }
}
