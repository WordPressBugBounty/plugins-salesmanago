<?php

namespace bhr\Admin\Entity;

if( !defined( 'ABSPATH' ) ) exit;

use SALESmanago\Entity\UnionConfigurationInterface;

/**
 * New Configuration entity with Product API attributes
 */
class Configuration extends \SALESmanago\Entity\Configuration implements UnionConfigurationInterface
{

    /**
     * @var null|string
     */
    protected $apiV3Key = null;

    /**
     * @var string
     */
    protected $apiV3Endpoint = 'https://api.salesmanago.com';

    /**
     * @var string
     */
    protected $Catalogs;

	/**
	 * @var string
	 */
	protected $activeCatalog = '';

	/**
	 * Flag - are there new api v3 errors - for displaying user notifications
	 * @var bool
	 */
	protected $isNewApiError = false;

    /**
     * @var string
     */
    protected $leadooScript = '';

	/**
	 * @var array<string, string>
	 */
	protected $multilocations = [];

	/**
	 * @var array<string, string>
	 */
	protected $activeCatalogsByLocation = [];

    /**
     * @return string|null
     */
    public function getApiV3Key()
    {
        return $this->apiV3Key;
    }

    /**
     * @param string $apiKey
     * @return $this
     */
    public function setApiV3Key( $apiKey )
    {
        $this->apiV3Key = $apiKey;
        return $this;
    }

	/**
	 * @return string
	 */
	public function getApiV3Endpoint()
	{
		return $this->apiV3Endpoint;
	}

    /**
     * @param string $endpoint
     * @return $this
     */
    public function setApiV3Endpoint( $endpoint )
    {
        $this->apiV3Endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string Catalog
     */
    public function getCatalogs()
    {
        return $this->Catalogs;
    }

    /**
     * @param string $catalogs
     *
     * @return $this
     */
    public function setCatalogs( $catalogs )
    {
        $this->Catalogs = $catalogs;
        return $this;
    }

	/**
	 * @return string
	 */
	public function getActiveCatalog()
	{
		return $this->activeCatalog;
	}

	/**
	 * @param string $catalog
	 *
	 * @return $this
	 */
	public function setActiveCatalog( $catalog )
	{
		$this->activeCatalog = $catalog;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNewApiError() {
		return $this->isNewApiError;
	}

    /**
     * @param string $script
     * @return Configuration
     */
    public function setLeadooScript($script) {
        $this->leadooScript = $script;
        return $this;
    }

    /**
     * @return string
     */
    public function getLeadooScript() {
        return $this->leadooScript;
    }

	/**
	 * @param  bool  $isNewApiError
	 */
	public function setIsNewApiError ( $isNewApiError ) {
		$this->isNewApiError = $isNewApiError;
		return $this;
	}

	/**
	 * @return array<string, string>
	 */
	public function getMultilocations(): array {
		return is_array($this->multilocations) ? $this->multilocations : [];
	}

	/**
	 * @param array<string, string> $multilocations
	 *
	 * @return $this
	 */
	public function setMultilocations( array $multilocations ) {
		$this->multilocations = $multilocations;
		return $this;
	}

	/**
	 * @return array<string, string>
	 */
	public function getActiveCatalogsByLocation(): array {
		return is_array($this->activeCatalogsByLocation) ? $this->activeCatalogsByLocation : [];
	}

	/**
	 * @param  array  $activeCatalogsByLocation
	 *
	 * @return $this
	 */
	public function setActiveCatalogsByLocation( array $activeCatalogsByLocation ) {
		$this->activeCatalogsByLocation = $activeCatalogsByLocation;
		return $this;
	}

    public function jsonSerialize(): array
    {
        $arr = parent::jsonSerialize();
        $arr['smApp'] = $this->getSmApp();
        $arr['multilocations'] = $this->getMultilocations();
        $arr['activeCatalogsByLocation'] = $this->getActiveCatalogsByLocation();
        return $arr;
    }
}