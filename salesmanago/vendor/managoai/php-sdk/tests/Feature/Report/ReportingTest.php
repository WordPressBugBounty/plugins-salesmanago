<?php

namespace Tests\Feature\Report;

use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use Tests\Feature\TestCaseFeature;
use SALESmanago\Model\Report\ReportModel;
use Faker\Factory;
use SALESmanago\Exception\Exception as SalesmanagoException;
use ReflectionClass;

class ReportingTest extends TestCaseFeature
{
    /**
     * Test checks that model build Contact Entity according to requirements
     *
     * @dataProvider provideReportModelGetClientAsModelStructure
     * @param mixed $configurationItem
     * @param mixed $expectedContactEntityAttribute
     * @return void
     */
    public function testReportModelGetClientAsModelStructureRight(
        $configurationItem,
        $expectedContactEntityAttribute
    ): void {
        $this->assertEquals($configurationItem, $expectedContactEntityAttribute);
    }

    /**
     * Test structure of reporting event
     *
     * @dataProvider provideTestGetActionLoginAsEvent
     * @param $configurationItem
     * @param $expectedContactEntityAttribute
     * @return void
     */
    public function testGetActionExportAsEventStructureRight($configurationItem, $expectedContactEntityAttribute): void
    {
        $this->assertEquals($configurationItem, $expectedContactEntityAttribute);
    }

    /**
     * Test that tags which assigned to Contact is right
     *
     * @return void
     */
    public function testReportingContactTagsStructureRight(): void
    {
        $Configuration = $this->generateConfiguration();
        $Contact = (new ReportModel($Configuration))
            ->getClientAsContact(ReportModel::ACT_LOGIN);

        $assignedTagsToContact = $Contact->getOptions()->getTags();

        $tags = [
            'lang' => 'LANG_' . str_replace(' ', '_', strtolower($Configuration->getPlatformLang())),
            'country' => str_replace(' ', '_', strtoupper($Configuration->getPlatformCountry())),
            'platformName' => str_replace(' ', '_', strtoupper($Configuration->getPlatformName())),
            'phpVersion' =>'PHP_' . $Configuration->getPhpVersion()
        ];

        foreach ($tags as $tag) {
            $this->assertContains($tag, $assignedTagsToContact);
        }
    }

    /**
     * Provide data for testReportModelGetClientAsModelStructureRight
     *
     * @return array[]
     */
    public function provideReportModelGetClientAsModelStructure(): array
    {
        $Configuration = $this->generateConfiguration();
        $Contact = (new ReportModel($Configuration))->getClientAsContact();

        return [
            [
                $Configuration->getOwner(),
                $Contact->getEmail()
            ], [
                $Configuration->getPlatformDomain(),
                $Contact->getName(),
            ], [
                $Configuration->getClientId(),
                $Contact->getExternalId(),
            ], [
                $Configuration->getPlatformName(),
                $Contact->getCompany(),
            ], [
                $Configuration->getPlatformLang(),
                $Contact->getOptions()->getLang()
            ]
        ];
    }

    /**
     * Provide data for testGetActionExportAsEventStructureRight
     *
     * @return array[]
     * @throws SalesmanagoException
     */
    public function provideTestGetActionLoginAsEvent(): array
    {
        $faker         = Factory::create();
        $Configuration = $this->generateConfiguration();
        $exportType    = $faker->randomElement([
            ReportModel::EXPORT_TYPE_PRODUCTS,
            ReportModel::EXPORT_TYPE_CARTS,
            ReportModel::EXPORT_TYPE_PURCHASE,
            ReportModel::EXPORT_TYPE_CONTACTS
        ]);

        $exportedPackages = $faker->randomNumber();
        $exportedItems    = $exportedPackages*100;
        $storeIdentifier  = $faker->company;

        $ReportModel = new ReportModel($Configuration);

        $actionToEventType = function () use ($ReportModel) {
            $reflectedClass = new ReflectionClass($ReportModel);
            $reflection = $reflectedClass->getProperty('actionToEventType');
            $reflection->setAccessible(true);
            return $reflection->getValue($ReportModel);
        };

        $Event = $ReportModel
            ->getActionAsEvent(
                ReportModel::ACT_EXPORT,
                [
                    ReportModel::EXPORT_TYPE => $exportType,
                    ReportModel::PACKAGES    => $exportedPackages,
                    ReportModel::ITEMS       => $exportedItems,
                    ReportModel::STORE       => $storeIdentifier
                ]
            );

        return [
            [
                $Event->getEmail(),
                $Configuration->getOwner()
            ],[
                $Event->getProducts(),
                $Configuration->getPlatformVersion(),
            ],[
                $Event->getLocation(),
                $Configuration->getVersionOfIntegration()
            ],[
                $Event->getShopDomain(),
                preg_replace("(^https?://)", "", $Configuration->getPlatformDomain())
            ],[
                $Event->getDetail(1),
                "php_version_{$Configuration->getPhpVersion()}"
            ],[
                $Event->getDetail(2),
                "plugin_version_{$Configuration->getVersionOfIntegration()}"
            ],[
                $Event->getContactExtEventType(),
                $actionToEventType()[ReportModel::ACT_EXPORT]
            ],[
                $Event->getDescription(),
                substr(json_encode($Configuration->toArray()), 0, 250)
            ], [
                $Event->getDetail(4),
                $exportType
            ], [
                $Event->getDetail(5),
                $exportedPackages
            ], [
                $Event->getDetail(6),
                $exportedItems
            ], [
                $Event->getDetail(7),
                $storeIdentifier
            ]
        ];
    }

    /**
     * Generate configuration for example vendor user (integration user)
     *
     * @return ConfigurationInterface
     */
    protected function generateConfiguration(): ConfigurationInterface
    {
        $faker           = Factory::create();
        $owner           = $faker->userName . '+test@' . $faker->freeEmailDomain;
        $generateVersion = function () use ($faker) {
            return $faker->randomDigit . '.' . $faker->randomDigit . '.' . $faker->randomDigit;
        };

        return Configuration::getInstance()
            ->setClientId($faker->sha1)
            ->setOwner($owner)
            ->setPlatformDomain($faker->domainName)
            ->setPlatformLang($faker->languageCode)
            ->setPlatformName($faker->domainName)
            ->setPlatformVersion($generateVersion())
            ->setVersionOfIntegration($generateVersion());
    }
}