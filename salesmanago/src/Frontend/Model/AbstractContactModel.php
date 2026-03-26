<?php

namespace bhr\Frontend\Model;

if(!defined('ABSPATH')) exit;

use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Address;
use SALESmanago\Entity\Contact\Options;
use SALESmanago\Entity\Contact\Properties;
use SALESmanago\Exception\Exception;

class AbstractContactModel extends AbstractModel
{
    protected $Contact;
    protected $Address;
    protected $Options;
    protected $Properties;

    protected $PlatformSettings;
    protected $PluginSettings;

    public function __construct($PlatformSettings, $PluginSettings) {
        parent::__construct();
        $this->Contact          = new Contact();
        $this->Address          = new Address();
        $this->Options          = new Options();
        $this->Properties       = new Properties();

        $this->PlatformSettings = $PlatformSettings;
        $this->PluginSettings   = $PluginSettings;

        $this->Contact->setAddress($this->Address);
        $this->Contact->setOptions($this->Options);
        $this->Contact->setProperties($this->Properties);
    }

    /**
     * @return Contact
     */
    public function get()
    {
        return $this->Contact;
    }

    /**
     *
     */
    public function setOptInStatuses()
    {
        try {
            if (!isset($this->Contact) || !isset($this->Options)) {
                throw new Exception('Contact or Options were not constructed');
            }

            //check if module newsletter is mapping an opt-in field
            if(!empty($this->PluginSettings->OptInInput->mode)
                && $this->PluginSettings->OptInInput->mode == 'map'
                && !empty($this->PluginSettings->OptInInput->mappedName)
            ) {
                $optInFieldName = $this->PluginSettings->OptInInput->mappedName;
            }
	        if(!empty($this->PluginSettings->OptInMobileInput->mode)
	           && $this->PluginSettings->OptInMobileInput->mode == 'map'
	           && !empty($this->PluginSettings->OptInMobileInput->mappedName)
	        ) {
		        $optInMobileFieldName = $this->PluginSettings->OptInMobileInput->mappedName;
	        }
	        $this->Contact->getOptions()
	                      ->setIsSubscriptionStatusNoChange(true);

            if($this->hasOptInValueInRequest([self::OPT_IN_EMAIL, $optInFieldName, 'sm_newsletter']))
            {
                $this->Options
                    ->setIsSubscribesNewsletter(true)
                    ->setIsSubscriptionStatusNoChange(false);
                $this->setTagsFromConfig(self::TAGS_NEWSLETTER);
            }

            if ($this->hasOptInValueInRequest([self::OPT_IN_MOBILE, $optInMobileFieldName])) {
                $this->Options
                    ->setIsSubscribesMobile(true)
                    ->setIsSubscriptionStatusNoChange(false);
            }
        } catch (Exception $e) {
            $e->getLogMessage();
        }
    }

    /**
     * Checks if any of the provided request field names contains a valid opt-in value.
     *
     * @param array $fieldNames List of request field names to check for opt-in value.
     * @return bool Returns true if any field contains a valid opt-in value, otherwise false.
     */
    private function hasOptInValueInRequest(array $fieldNames): bool
    {
        foreach ($fieldNames as $fieldName) {
            if (!empty($fieldName) && isset($_REQUEST[$fieldName]) && $this->isOptInValue($_REQUEST[$fieldName])) {
                return true;
            }
        }
        return false;
    }

    /**
    * Check if opt-in value is handling both array and scalar values.
    * Arrays are supported for form builders that send checkbox values as arrays (e.g. Ultimate Member).
    *
    * @param mixed $value
    * @return bool
    */
    private function isOptInValue($value)
    {
        if (is_array($value)) {
            return !empty(array_filter($value));
        }
        return !empty($value);
    }

    /**
     *
     */
    public function setLanguage()
    {
        if(isset($this->PlatformSettings->languageDetection)) {
            if($this->PlatformSettings->languageDetection === 'browser') {
                $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            } else {
                $lang = substr(Helper::getUserLocale(), 0, 2);
            }
            $this->Options->setLang(strtoupper($lang));
        }
    }

	/**
	 * @param $propertiesMap
	 *
	 * @return array
	 */
	protected function getPropertiesAsDetails($propertiesMap)
	{
		$details = array();
		foreach ($propertiesMap as $name => $value) {
			$value = is_array($value) ? implode(',', $value) : $value;
			$value = (strlen($value) > 255) ? substr($value, 0, 255) : $value;

			if (!empty($name) && !empty($value)) {
				$details[$name] = $value;
			}
		}
		return $details;
	}

	/**
	 * @param $propertiesMap
	 * @param false $addNames
	 *
	 * @return array|string
	 */
	protected function getPropertiesAsTags($propertiesMap, $addNames = false)
	{
		$tags = array();
		foreach ($propertiesMap as $name => $value) {
			if(is_array($value)) {
				foreach ($value as $subValue) {
					$tags[] = $addNames
						? $name . '-' . $subValue
						: $subValue;
				}
			} else {
				$tags[] = $addNames
					? $name . '-' . $value
					: $value;
			}

		}
		return Helper::clearCSVInput($tags, false, true, true, ',', 255);
	}

	protected function setPropertiesAsMappedType($propertiesMappingMode, $propertiesMap)
	{
		switch ($propertiesMappingMode) {
			case 'details':
				$this->Properties->setItems($this->getPropertiesAsDetails($propertiesMap));
				break;
			case 'tagValues':
				$this->Options->appendTags($this->getPropertiesAsTags($propertiesMap, false));
				break;
			case 'tagNamesValues':
				$this->Options->appendTags($this->getPropertiesAsTags($propertiesMap, true));
				break;
		}
	}

	/**
	 * @param $tagsType
	 * @return Contact
	 */
	public function setTagsFromConfig($tagsType)
	{
		$tags = isset($this->PluginSettings->tags->$tagsType)
			? $this->PluginSettings->tags->$tagsType
			: null;
		if(!empty($tags)) {
			$tags = explode(',', $tags);
			$this->Options->appendTags($tags);
		}
		return $this->Contact;
	}
}
