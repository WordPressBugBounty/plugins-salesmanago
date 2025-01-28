<?php

namespace SALESmanago\Helper\Mapper;

use SALESmanago\Helper\Mapper\BuilderInterface;
use ReflectionClass;
use SALESmanago\Helper\Mapper\Map\Entity as MapEntity;
use Exception;

class Builder implements BuilderInterface
{
    /**
     * Build object from map and adapter
     *
     * @param mixed $toObject
     * @param string $map
     * @param AdapterInterface|null $adapter
     * @param null|mixed $fromObject
     * @param null|mixed $toObject
     * @return mixed
     * @throws Exception
     */
    public function build(
        $toObject,
        string $map, AdapterInterface $adapter = null,
        $fromObject = null
    ) {
        //deserialize map:
        $MapEntity = MapEntity::jsonDeserialize($map);

        //if no adapter provided, use default one:
        if ($adapter === null) {
            $adapter = new Adapter();
        }

        //get map fields:
        $mapItems = $MapEntity->getFields();

        //map fields:
        foreach ($mapItems as $objAttrName => $mapItem ) {
            //create method name:
            $objMethod = 'set' . ucfirst($objAttrName);

            //check if object has method:
            $this->checkObjectMethod($toObject, $objMethod);

            //get adapter method name if exists:
            $adapterMethodName = $adapter->getAdapterMethodForField($mapItem->getName());

            //call adapter method if exists:
            if ($this->checkAdapterMethod($adapter, $adapterMethodName)) {
                //use adapter method to map field to object:
                $toObject = $adapter->$adapterMethodName($toObject, $objMethod, $mapItem, $fromObject);
            } else {
                //map field to object:
                $adapter->assign($toObject, $objMethod, $mapItem);
            }
        }

        return $toObject;
    }

    /**
     * Check if object has method
     *
     * @param $toObject
     * @param string $objMethod
     * @return void
     * @throws \Exception
     */
    private function checkObjectMethod($toObject, string $objMethod)
    {
        //check if object has method:
        $reflection = new ReflectionClass($toObject);
        if (!method_exists($toObject, $objMethod)
            && !$reflection->hasMethod('__call')
        ) {
            throw new \Exception('Method ' . $objMethod . ' does not exist in ' . get_class($toObject));
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