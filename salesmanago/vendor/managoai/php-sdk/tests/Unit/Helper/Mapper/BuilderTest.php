<?php

namespace Tests\Unit\Helper\Mapper;

use SALESmanago\Helper\Mapper\Adapter;
use SALESmanago\Helper\Mapper\AdapterInterface;
use SALESmanago\Helper\Mapper\Builder;
use \stdClass;
use Tests\Unit\TestCaseUnit;
use Exception;

class BuilderTest extends TestCaseUnit
{
    /**
     * Test build success
     *
     * @covers \SALESmanago\Helper\Mapper\Builder::build
     * @return void
     * @throws Exception
     */
    public function testBuildWithoutAdapterSuccess() : void {
        //create builder:
        $builder = new Builder();

        //create object with magic methods to check mapper:
        $toObject = $this->createObjectToMap();

        //generate map:
        $map = $this->generateMapArray();
        $map = json_encode($map);

        //build object based on mapped fields:
        $obj = $builder->build($toObject, $map);

        $mapArr = json_decode($map, true);

        foreach ($mapArr as $objAttrName => $mapItem ) {
            $methodName = 'get' . ucfirst($objAttrName);
            $this->assertNotEmpty($obj->$methodName());
        }
    }

    /**
     * Build object with adapter method success
     *
     * @covers \SALESmanago\Helper\Mapper\Builder::build
     * @return void
     * @throws Exception
     */
    public function testBuildWithAdapterMethodSuccess(): void
    {
        //create builder:
        $builder = new Builder();

        //create object with magic methods to check mapper:
        $toObject = $this->createObjectToMap();

        //generate map:
        $map = $this->generateMapArray();

        //create adapter:
        $adapter = $this->createAdapterWithMethods($map);

        $map = json_encode($map);

        //build object based on mapped fields:
        $obj = $builder->build($toObject, $map, $adapter);

        //prepare assertion:
        $mapArr = json_decode($map, true);

        //foreach mapped field, check if adapted value is set:
        foreach ($mapArr as $objAttrName => $mapItem ) {
            $methodName = 'get' . ucfirst($objAttrName);
            $this->assertTrue(strpos($obj->$methodName(), 'test_adapted') !== false);
        }
    }

    /**
     * Create Adapter with methods
     *
     * @param array $mapArray
     * @return AdapterInterface
     */
    public function createAdapterWithMethods(array $mapArray): AdapterInterface
    {
        $generatedAdaptedFields = [];

        foreach ($mapArray as $objAttrName => $mapItem) {
            $generatedAdaptedFields[$mapItem['name']] = 'adapt' . ucfirst($mapItem['name']);
        }

        return new class($generatedAdaptedFields) extends Adapter {
            public array $adaptedFields = [];

            public function __construct($generatedAdaptedFields)
            {
               $this->adaptedFields = $generatedAdaptedFields;
            }

            //magic methods to set and get properties:
            public function __call($methodName, $arguments) {
                if (strpos($methodName, 'adapt') === 0
                    && array_key_exists(lcfirst(substr($methodName, 5)), $this->adaptedFields))
                {
                    $toObject  = $arguments[0];
                    $objMethod = $arguments[1];
                    $mapItem   = $arguments[2];
                    $value     = $mapItem->getValue().'test_adapted';

                    //test_adapted add as simulation of modified value in adapter method:
                    return $toObject->$objMethod($value);
                }

                throw new Exception("Method {$methodName} does not exist.");
            }
        };
    }

    /**
     * Create object to be mapped
     *
     * @return stdClass
     */
    public function createObjectToMap(): stdClass
    {
        //create object with magic methods to check mapper:
        return new class extends stdClass {
            private $properties = [];

            //magic methods to set and get properties:
            public function __call($name, $arguments) {
                if (strpos($name, 'set') === 0) {
                    $property = lcfirst(substr($name, 3)); // Get the property name from the method
                    $this->properties[$property] = $arguments[0];
                } elseif(strpos($name, 'get') === 0) {
                    $property = lcfirst(substr($name, 3)); // Get the property name from the method
                    return $this->properties[$property];
                } else {
                    throw new Exception("Method $name does not exist.");
                }

                return $this;//allow method chaining
            }
        };
    }

    /**
     * Generate map
     *
     * @return array
     */
    public function generateMapArray(): array
    {
        //generate map for static keys:
        return [
            'brand'         => $this->generateMapItem(),
            'manufacturer'  => $this->generateMapItem(),
            'season'        => $this->generateMapItem(),
            'color'         => $this->generateMapItem(),
            'popularity'    => $this->generateMapItem(),
            'bestseller'    => $this->generateMapItem(),
            'newProduct'    => $this->generateMapItem(),
            'gender'        => $this->generateMapItem(),
            'categories'    => $this->generateMapItem(),
            'discountPrice' => $this->generateMapItem(),
            'detail1'       => $this->generateMapItem(),
            'detail2'       => $this->generateMapItem(),
            'detail3'       => $this->generateMapItem(),
            'detail4'       => $this->generateMapItem(),
            'detail5'       => $this->generateMapItem(),
        ];
    }

    /**
     * Generate Map Item
     *
     * @return array
     */
    public function generateMapItem(): array
    {
        //types of items which describes product:
        $types = ['attributes', 'configuration_item', 'option'];
        $type = $types[array_rand($types)];

        //field name
        $name = $this->faker->word;

        //map item:
        return [
            'name'  => $name,
            'value' => $type . '_' . $name,
            'label' => ucfirst($type) . ucfirst($name),
        ];
    }
}