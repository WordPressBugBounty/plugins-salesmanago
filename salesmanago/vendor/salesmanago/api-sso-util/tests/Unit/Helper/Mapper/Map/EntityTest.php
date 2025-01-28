<?php

namespace Tests\Unit\Helper\Mapper\Map;

use SALESmanago\Helper\Mapper\Map\Entity;
use SALESmanago\Helper\Mapper\Map\ItemEntity;
use Tests\Unit\TestCaseUnit;

class EntityTest extends TestCaseUnit
{
    /**
     * Test setField
     *
     * @covers Entity::setField
     */
    public function testSetField(): void
    {
        $entity = new Entity();
        $item = new ItemEntity();

        $fieldName = $this->faker->word;

        $entity->setField($fieldName, $item);

        $this->assertEquals($item, $entity->getField($fieldName));
    }

    /**
     * Test getFields
     *
     * @covers Entity::getFields
     */
    public function testGetFields(): void
    {
        $entity = new Entity();
        $item = new ItemEntity();

        $fieldName = $this->faker->word;

        $entity->setField($fieldName, $item);
        $this->assertEquals([$fieldName => $item], $entity->getFields());
    }

    /**
     * Test jsonSerialize
     *
     * @covers Entity::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        $entity = new Entity();
        $item = new ItemEntity();

        $fieldName = $this->faker->word;
        $itemName = $this->faker->word;
        $itemLabel = $this->faker->word;

        $item->setName($itemName);
        $item->setValue($itemName);
        $item->setLabel($itemLabel);

        $entity->setField($fieldName, $item);

        $this->assertEquals(
            [$fieldName => ['name' => $itemName, 'value' => $itemName, 'label' => $itemLabel]],
            $entity->jsonSerialize()
        );
    }

    /**
     * Test jsonDeserialize
     *
     * @covers Entity::jsonDeserialize
     */
    public function testJsonDeserialize(): void
    {
        $entity = new Entity();
        $item = new ItemEntity();

        $fieldName = $this->faker->word;
        $itemName = $this->faker->word;

        $item->setName($itemName);
        $item->setValue($itemName);
        $item->setLabel($itemName);

        $entity->setField($fieldName, $item);

        $json = json_encode($entity);

        $this->assertEquals($entity, Entity::jsonDeserialize($json));
    }
}