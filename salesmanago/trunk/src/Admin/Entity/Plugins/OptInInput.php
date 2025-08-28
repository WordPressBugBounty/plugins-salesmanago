<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\AbstractEntity;
use bhr\Admin\Model\Helper;

class OptInInput extends AbstractEntity
{
    const
        DEFAULT_NEWSLETTER_LABEL       = 'Subscribe to our newsletter',
        DEFAULT_MOBILE_LABEL           = 'Subscribe to Mobile Marketing',
        DEFAULT_NEWSLETTER_MAPPED_NAME = 'sm-optin',
        DEFAULT_MOBILE_MAPPED_NAME     = 'sm-optin-mobile';

    protected $mode = 'none';
    protected $label;
    protected $mappedName;

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getMappedName()
    {
        return $this->mappedName;
    }

    /**
     * @param string $mappedName
     */
    public function setMappedName($mappedName)
    {
        $this->mappedName = $mappedName;
        return $this;
    }

    public function setOptInInput($optInInput, $mobile = false)
    {
        if(is_array($optInInput)) {
            $this->mode       = isset($optInInput['mode']) ? $optInInput['mode'] : 'none';
            $this->label      = isset($optInInput['label'])
                ? $optInInput['label']
                : ($mobile ? self::DEFAULT_MOBILE_LABEL : self::DEFAULT_NEWSLETTER_LABEL);
            $this->mappedName = isset($optInInput['name'])
                ? $optInInput['name']
                : ($mobile ? self::DEFAULT_MOBILE_MAPPED_NAME : self::DEFAULT_NEWSLETTER_MAPPED_NAME);
        } else {
            $this->mode = isset($optInInput->mode) ? $optInInput->mode : 'none';
            $this->label = isset($optInInput->label)
                ? $optInInput->label
                : ($mobile ? self::DEFAULT_MOBILE_LABEL : self::DEFAULT_NEWSLETTER_LABEL);
            $this->mappedName = isset($optInInput->mappedName)
                ? $optInInput->mappedName
                : ($mobile ? self::DEFAULT_MOBILE_MAPPED_NAME : self::DEFAULT_NEWSLETTER_MAPPED_NAME);
        }
        if($this->mode === 'append' || $this->mode === 'appendEverywhere') {
			if (!$mobile) {
				Helper::iclRegisterString( 'salesmanago', 'OptInInputLabel', $this->mappedName );
			} else {
				Helper::iclRegisterString( 'salesmanago', 'OptInMobileInputLabel', $this->mappedName );
			}

        }
        return $this;
    }

    public function setLegacyOptInInput($optInInput, $active = false, $mobile = false)
    {
        $mode = 'none';
		if (!$mobile && !empty($optInInput->type)) {
			switch ($optInInput->type) {
				case 'newsletter':
					$mode = 'append';
					break;
				case 'mapper':
					$mode = 'map';
					break;
			}
		}
        $label = isset($optInInput->newsletterContent->default) ? $optInInput->newsletterContent->default : 'Subscribe to our newsletter';
        $mappedName = isset($optInInput->mappedName) ? $optInInput->mappedName : 'sm-optin';

        if(!$active) {
            $mode = 'none';
        }
        $this->setOptInInput(array(
            'mode' => $mode,
            'label' => $label,
            'mappedName' => $mappedName
        ));
    }
}
