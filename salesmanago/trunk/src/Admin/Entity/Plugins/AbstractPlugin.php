<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\AbstractEntity;
use bhr\Admin\Model\Helper;
use bhr\Admin\Entity\Configuration;

class AbstractPlugin extends AbstractEntity
{
	const
		DEFAULT_PROPERTY_TYPE = 'details';

	protected $active = false;
    protected $DoubleOptIn;
    protected $OptInInput;
	protected $OptInMobileInput;

    protected $tags = array();

    protected $owner = '';
    protected $forms = array();
    protected $properties = array();
	protected $propertiesMappingMode = self::DEFAULT_PROPERTY_TYPE;

    public function __construct()
    {
        $this->DoubleOptIn      = new DoubleOptIn();
        $this->OptInInput       = new OptInInput();
		$this->OptInMobileInput = new OptInInput();
    }

    public function setPluginSettings($pluginSettings)
    {
        $this->active = isset($pluginSettings->active)
            ? $pluginSettings->active
            : false;

        $this->properties = (isset($pluginSettings->properties) && !Helper::isEmpty($pluginSettings->properties))
            ? $pluginSettings->properties
            : array();

        $this->owner = (isset($pluginSettings->owner))
            ? $pluginSettings->owner
            : Configuration::getInstance()->getOwner();

        $this->getDoubleOptIn()->setDoubleOptIn(
            isset($pluginSettings->DoubleOptIn) ? $pluginSettings->DoubleOptIn : ''
        );

        $this->getOptInInput()->setOptInInput(
            isset($pluginSettings->OptInInput) ? $pluginSettings->OptInInput : ''
        );

		$this->getOptInMobileInput()->setOptInInput(
            isset($pluginSettings->OptInMobileInput) ? $pluginSettings->OptInMobileInput : '',
			true
        );

        $this->setForms(isset($pluginSettings->forms) ? $pluginSettings->forms : array());

        $this->tags['login'] = isset($pluginSettings->tags->login)
            ? self::prepareTags($pluginSettings->tags->login)
            : '';

        $this->tags['registration'] = isset($pluginSettings->tags->registration)
            ? self::prepareTags($pluginSettings->tags->registration)
            : '';

        $this->tags['newsletter'] = isset($pluginSettings->tags->newsletter)
            ? self::prepareTags($pluginSettings->tags->newsletter)
            : '';

        $this->tags['purchase'] = isset($pluginSettings->tags->purchase)
            ? self::prepareTags($pluginSettings->tags->purchase)
            : '';

        $this->tags['guestPurchase'] = isset($pluginSettings->tags->guestPurchase)
            ? self::prepareTags($pluginSettings->tags->guestPurchase)
            : '';

		$this->propertiesMappingMode = isset($pluginSettings->propertiesMappingMode)
	        ? $pluginSettings->propertiesMappingMode
	        : '';

        return $this;
    }

    /**
     * @return DoubleOptIn
     */
    public function getDoubleOptIn()
    {
        return isset($this->DoubleOptIn) ? $this->DoubleOptIn : new DoubleOptIn();
    }

    /**
     * @param DoubleOptIn $DoubleOptIn
     */
    public function setDoubleOptIn($DoubleOptIn)
    {
        $this->DoubleOptIn = $DoubleOptIn;
        return $this;
    }

    /**
     * @return OptInInput
     */
    public function getOptInInput()
    {
        return isset($this->OptInInput) ? $this->OptInInput : new OptInInput();
    }

    /**
     * @param OptInInput $OptInInput
     */
    public function setOptInInput($OptInInput)
    {
        $this->OptInInput = $OptInInput;
        return $this;
    }

	/**
	 * @return OptInInput
	 */
	public function getOptInMobileInput()
	{
		return $this->OptInMobileInput;
	}

	/**
	 * @param OptInInput $OptInMobileInput
	 *
	 * @return AbstractPlugin
	 */
	public function setOptInMobileInput($OptInMobileInput)
	{
		$this->OptInMobileInput = $OptInMobileInput;
		return $this;
	}

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties($properties)
    {
        if(is_array($properties)) {
            $temp = array();
            foreach($properties as $item) {
                if(!empty($item)) {
                    $temp[] = trim($item); //filter_array is not working for classic arrays and makes them assoc on json export
                }
            }
            $this->properties = $temp;
        } else {
            $this->properties = array(trim($properties));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getForms()
    {
        return empty($this->forms) ? array() : $this->forms;
    }

	/**
	 * @param array $forms
	 *
	 * @return AbstractPlugin
	 */
    public function setForms($forms = array())
    {
        foreach($forms as $key => $form) {
            $this->setForm($key, $form);
        }
        return $this;
    }

    public function deleteForms($forms = array())
    {
        unset($this->forms);
        return $this;
    }

	/**
	 * @param $id
	 * @param $form
	 *
	 * @return AbstractPlugin
	 */
    public function setForm($id, $form)
    {
        if((int) $id && is_array($form) && !empty($form['owner'])) {
            $owner        = $form['owner'];
            $tags         = $form['tags'];
            $tagsToRemove = $form['tagsToRemove'];
        } elseif ((int) $id && !empty($form->owner)) {
            $owner        = $form->owner;
            $tags         = $form->tags;
            $tagsToRemove = $form->tagsToRemove;
        } else {
            return $this;
        }

        $this->forms[(int) $id] = array(
            'owner'        => $owner,
            'tags'         => self::prepareTags($tags),
            'tagsToRemove' => self::prepareTags($tagsToRemove)
        );

        return $this;
    }


	/**
	 * @param $formsRequest
	 *
	 * @return AbstractPlugin
	 */
    public function setFormsFromRequest($formsRequest)
    {
        if(!Helper::isEmpty($formsRequest)) {
            foreach ($formsRequest as $key=>$form) {
                $id = (int) $key;
                if($id>0 && isset($form['owner'])) {
                    $this->setForm($id, array(
                        'owner'        => $form['owner'],
                        'tags'         => $form['tags-to-add'],
                        'tagsToRemove' => $form['tags-to-remove']
                    ));
                }
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active = false)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getTagsByType($type)
    {
        return isset($this->tags[$type])
            ? $this->tags[$type]
            : "";
    }

	/**
	 * @param $type
	 * @param $value
	 *
	 * @return AbstractPlugin
	 */
    public function setTagsByType($type, $value)
    {
        if(in_array($type, parent::$tagsTypes)) {
            $this->tags[$type] = self::prepareTags($value);
        }
        return $this;
    }

	/**
	 * @param $tagsArray
	 *
	 * @return AbstractPlugin
	 */
    public function setTags($tagsArray)
    {
        if(is_array($tagsArray)) {
            foreach($tagsArray as $type => $tags) {
                if(!in_array($type, parent::$tagsTypes)) {
                    continue;
                }
                $this->tags[$type] = self::prepareTags($tags);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

	/**
	 * @param $input
	 *
	 * @return array|string
	 */
	public static function prepareTags($input)
    {
        return Helper::clearCSVInput($input, true, true, true, ', ');
    }

	/**
	 * @param array $forms
	 * @param array $availableFormsList
	 */
	public function setLegacyForms($forms = array(), $availableFormsList = array())
    {
        foreach ($forms as $form)
        {
            if(empty($form->name)
                || empty($form->owner)) {
                continue;
            }
            $id = array_search($form->name, $availableFormsList);
            if($id !== false) {
                self::setForm($id, array(
                    "owner"        => $form->owner,
                    "tags"         => $form->tags,
                    "tagsToRemove" => $form->removeTags,
                ));
            }
        }
    }

	/**
	 * @param array $properties
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setLegacyProperties($properties = array(), $options = array())
    {
        $properties = array_merge($properties, $options);
        foreach ($properties as $property)
        {
            $this->properties[] = $property;
        }
        return $this;
    }

	/**
	 * @return string
	 */
	public function getPropertiesMappingMode() {
		return $this->propertiesMappingMode;
	}

	/**
	 * @param string $propertiesMappingMode
	 *
	 * @return AbstractPlugin
	 */
	public function setPropertiesMappingMode( $propertiesMappingMode ) {
		$this->propertiesMappingMode = $propertiesMappingMode;
		return $this;
	}
}
