<?php

namespace SALESmanago\Entity\Api\V3;

use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Exception\Exception;
use SALESmanago\Helper\DataHelper;

class CatalogEntity extends AbstractEntity implements CatalogEntityInterface
{
    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntity::$id
     * @var string
     */
    private $catalogId;

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntity::$name
     * @var string
     */
    private $catalogName;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string the same as catalog name
     */
    private $name;

    /**
     * @var bool
     */
    private $setAsDefault;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $location;

    /**
     * @param array|null $data - key => value array, where key is a CatalogEntity attribute;
     * @throws Exception
     */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->setDataWithSetters($data);
        }
    }

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntity::getId()
     * @return string
     */
    public function getCatalogId()
    {
        return $this->id;
    }

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntity::setId()
     * @param string $id
     * @return CatalogEntity
     */
    public function setCatalogId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return CatalogEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntity::getName()
     * @return mixed
     */
    public function getCatalogName()
    {
        return $this->name;
    }

    /**
     * @deprecated
     * @see \SALESmanago\Entity\Api\V3\CatalogEntity::setName()
     * @param string $name
     * @return CatalogEntity
     */
    public function setCatalogName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CatalogEntity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSetAsDefault()
    {
        return $this->setAsDefault;
    }

    /**
     * @param bool $setAsDefault
     * @return CatalogEntity
     */
    public function setSetAsDefault($setAsDefault)
    {
        $this->setAsDefault = $setAsDefault;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return CatalogEntity
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return CatalogEntity
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return DataHelper::filterDataArray([
            "catalogId"    => $this->id,
            "name"         => $this->name,
            "setAsDefault" => $this->setAsDefault,
            "currency"     => $this->currency,
            "location"     => $this->location
        ]);
    }
}
