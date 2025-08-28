<?php

namespace SALESmanago\Entity\Api\V3;

use JsonSerializable;

interface CatalogEntityInterface extends JsonSerializable
{
    const CATALOG_ID = 'catalogId';

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntityInterface::getId()
     * @return string
     */
    public function getCatalogId();

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntityInterface::setId()
     * @param string $catalogId
     * @return CatalogEntityInterface
     */
    public function setCatalogId($catalogId);

    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $catalogId
     * @return CatalogEntityInterface
     */
    public function setId($catalogId);

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntityInterface::setName()
     * @param string $name
     * @return CatalogEntityInterface
     */
    public function setCatalogName($name);

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntityInterface::getName()
     * @return mixed
     */
    public function getCatalogName();

    /**
     * @param string $name
     * @return CatalogEntityInterface
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return bool
     */
    public function getSetAsDefault();

    /**
     * @param bool $setAsDefault
     * @return CatalogEntityInterface
     */
    public function setSetAsDefault($setAsDefault);

    /**
     * @return mixed
     */
    public function getCurrency();

    /**
     * @param string $currency
     * @return CatalogEntityInterface
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getLocation();

    /**
     * @param string $location
     * @return CatalogEntityInterface
     */
    public function setLocation($location);
}
