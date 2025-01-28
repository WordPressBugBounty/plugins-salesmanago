<?php

namespace SALESmanago\Helper\Mapper;

use SALESmanago\Entity\Api\V3\Product\ProductEntity;
use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Entity\Api\V3\Product\SystemDetailsEntity;
use SALESmanago\Entity\Api\V3\Product\SystemDetailsInterface;
use SALESmanago\Entity\DetailsInterface;
use SALESmanago\Helper\Mapper\BuilderInterface;
use ReflectionClass;
use SALESmanago\Helper\Mapper\Map\Entity as MapEntity;
use Exception;
use SALESmanago\Helper\Mapper\Map\ItemEntityInterface;
use SALESmanago\Helper\Mapper\ProductEntityAdapter;

class ProductEntityBuilder implements BuilderInterface
{
    /**
     * Build object from map and adapter
     *
     * @param mixed $toObject
     * @param string $map
     * @param ProductEntityAdapter|null $adapter
     * @param null|mixed $fromObject
     * @param null|mixed $toObject
     * @return mixed
     * @throws Exception
     */
    public function build(
        $productEntity,
        string $map,
        AdapterInterface $adapter = null,
        $fromObject = null
    ) {
        //deserialize map:
        $MapEntity = MapEntity::jsonDeserialize($map);

        //if no adapter provided, use default one:
        if ($adapter === null) {
            $adapter = new ProductEntityAdapter();
        }

        //get map fields:
        $mapItems = $MapEntity->getFields();

        //get system and custom details:
        $systemDetails = $productEntity->getSystemDetails();
        $customDetails = $productEntity->getCustomDetails();

        //map fields:
        foreach ($mapItems as $objAttrName => $mapItem ) {
            //create method name:
            $objMethod = $this->generateObjectSetMethodName($objAttrName);

            //check system and custom details:
            if (method_exists($systemDetails, $objMethod)) {
                $this->assign($systemDetails, $objAttrName, $objMethod, $mapItem, $adapter, $fromObject);
            } elseif (preg_match('/^setDetail[1-5]$/', $objMethod)
                && method_exists($customDetails, 'set')
            ) {
                $this->assign($customDetails, $objAttrName, $objMethod, $mapItem, $adapter, $fromObject);
            } else {
                $this->assign($productEntity, $objAttrName, $objMethod, $mapItem, $adapter, $fromObject);
            }
        }

        //reset sytem and custom details and return entity:
        return $productEntity
            ->setSystemDetails($systemDetails)
            ->setCustomDetails($customDetails);
    }

    /**
     * Generate object set method name
     *
     * @param string|null $objectAttrubuteName
     * @return string
     */
    protected function generateObjectSetMethodName(?string $objectAttributeName)
    {
        if (strpos($objectAttributeName, '_') !== false) {
            $parts = explode('_', $objectAttributeName);
            $parts = array_map(function($part, $index) {
                return $index === 0 ? $part : ucfirst($part);
            }, $parts, array_keys($parts));
            $objectAttributeName = implode('', $parts);
        }

        return 'set' . ucfirst($objectAttributeName);
    }

    /**
     * Uses adapter to assign value to object
     *
     * @param mixed $object
     * @param string $method
     * @param ItemEntityInterface $mapItem
     * @param AdapterInterface $adapter
     * @param $fromObject
     * @return void
     * @throws Exception
     */
    private function assign(
        &$object,
        $objAttrName,
        $method,
        $mapItem,
        AdapterInterface $adapter,
        $fromObject
    ) {
        //check if object has method:
        $this->checkObjectMethod($object, $method);

        //get adapter method name if exists:
        $adapterMethodName = $adapter->getAdapterMethodForField($objAttrName);

        //call adapter method if exists:
        if ($this->checkAdapterMethod($adapter, $adapterMethodName)) {
            //use adapter method to map field to object:
            $object = $adapter->$adapterMethodName($object, $method, $mapItem, $fromObject);
        } else {
            //map field to object:
            $adapter->assign($object, $method, $mapItem);
        }
    }

    /**
     * Check if object has method
     *
     * @param ProductEntityInterface|SystemDetailsInterface|DetailsInterface $toObject
     * @param string $objMethod
     * @return void
     * @throws Exception
     */
    private function checkObjectMethod($toObject, string $objMethod)
    {
        //check if object has method:
        $reflection = new ReflectionClass($toObject);

        if (!method_exists($toObject, $objMethod)
            && !$reflection->hasMethod('__call')
            && !$reflection->hasMethod('set') //for custom details
        ) {
            throw new Exception('Method ' . $objMethod . ' does not exist in ' . get_class($toObject));
        }
    }

    /**
     * Check if adapter has method
     *
     * @param $adapter
     * @param string $adapterMethodName
     * @return bool
     */
    private function checkAdapterMethod(AdapterInterface $adapter, ?string $adapterMethodName): bool
    {
        if (empty($adapterMethodName)) {
            return false;
        }

        $reflection = new ReflectionClass($adapter);

        return (in_array($adapterMethodName, $adapter->getAdaptedFields())
            && (method_exists($adapter, $adapterMethodName) || $reflection->hasMethod('__call'))
        );
    }
}
