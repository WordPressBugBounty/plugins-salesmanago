<?php


namespace Tests\Feature\Services;

use Exception;
use SALESmanago\Controller\LoginController;
use SALESmanago\Entity\AbstractConfiguration;
use SALESmanago\Entity\User;
use Tests\Feature\TestCaseFeature;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Services\Report\ReportService;
use SALESmanago\Entity\Configuration;
use SALESmanago\Model\Report\ReportModel;
use Faker;

class ReportServiceTest extends TestCaseFeature
{

    public function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionProperty(AbstractConfiguration::class, 'instances');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);

        $reflection = new \ReflectionProperty(ReportService::class, 'instances');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }

    /**
     * Test Report test Action
     *
     * @throws Exception
     */
    public function testReportActionSuccess()
    {
        $this->faker = Faker\Factory::create();
        $conf = $this->initConf();

        $conf
            ->setActiveReporting(true)
            ->setPlatformName($this->faker->word)
            ->setPlatformVersion($this->generateVersion())
            ->setVersionOfIntegration($this->generateVersion())
            ->setPlatformDomain($this->faker->languageCode)
            ->setPlatformDomain($this->faker->url);

        $response = ReportService::getInstance($conf)
            ->reportAction(ReportModel::ACT_TEST, [$this->faker->text(2000)]);

        $this->assertTrue($response);
    }

    /**
     * @return string
     */
    protected function generateVersion()
    {
        $this->faker = Faker\Factory::create();
        return $this->faker->numberBetween(1, 10) . '.' . $this->faker->numberBetween(1, 10) . '.' . $this->faker->numberBetween(1, 10);
    }
}