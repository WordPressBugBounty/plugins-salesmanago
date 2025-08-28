<?php

namespace Tests\Unit;

use  PHPUnit\Framework\TestCase;
use Faker;
class TestCaseUnit extends TestCase
{
    /**
     * @var Faker\Generator
     */
    public $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();
    }
}