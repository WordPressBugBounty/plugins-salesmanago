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
	 * @param  bool  $isNewApiError
	 */
	public function setIsNewApiError ( $isNewApiError ) {
		$this->isNewApiError = $isNewApiError;
	}

    public function jsonSerialize(): array
    {
        $arr = parent::jsonSerialize();
        $arr['smApp'] = $this->getSmApp();
        return $arr;
    }
}