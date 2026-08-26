<?php

namespace Tests\Unit\Helper\Mapper\Map;

use Tests\Unit\TestCaseUnit;
use \SALESmanago\Helper\Mapper\Map\ItemEntity;
class ItemEntityTest extends TestCaseUnit
{
    /**
     * Test setName
     *
     * @return void
     */
    public function testSetName(): void
    {
        $item = new ItemEntity();

        $name = $this->faker->word;
        $item->setName($name);

        $this->assertEquals($name, $item->getName());
    }

    /**
     * Test getValue
     *
     * @return void
     */
    public function testGetValue(): void
    {
        $item = new ItemEntity();

        $value = $this->faker->word;
        $item->setValue($value);

        $this->assertEquals($value, $item->getValue());
    }

    /**
     * Test setLabel
     *
     * @return void
     */
    public function testSetLabel(): void
    {
        $item = new ItemEntity();

        $label = $this->faker->word;
        $item->setLabel($label);

        $this->assertEquals($label, $item->getLabel());
    }

    /**
     * Test jsonSerialize
     *
     * @return void
     */
    public function testJsonSerialize(): void
    {
        $item = new ItemEntity();

        $name = $this->faker->word;
        $value = $this->faker->word;
        $label = $this->faker->word;

        $item->setName($name);
        $item->setValue($value);
        $item->setLabel($label);

        $this->assertEquals([
            'name' => $name,
            'value' => $value,
            'label' => $label
        ], $item->jsonSerialize());
    }

    /**
     * Test jsonDeserialize
     *
     * @return void
     */
    public function testJsonDeserialize(): void
    {
        $name = $this->faker->word;
        $value = $this->faker->word;
        $label = $this->faker->word;

        $json = json_encode([
            'name' => $name,
            'value' => $value,
            'label' => $label
        ]);

        $item = ItemEntity::jsonDeserialize($json);

        $this->assertEquals($name, $item->getName());
        $this->assertEquals($value, $item->getValue());
        $this->assertEquals($label, $item->getLabel());
    }
}