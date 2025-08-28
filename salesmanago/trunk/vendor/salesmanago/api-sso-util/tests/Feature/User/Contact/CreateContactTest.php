<?php

namespace Tests\Feature\User\Contact;

use SALESmanago\Controller\ContactAndEventTransferController;
use SALESmanago\Entity\ApiDoubleOptIn;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Exception\Exception;
use Tests\Feature\ContactHelper;
use Tests\Feature\TestCaseFeature;

class CreateContactTest extends TestCaseFeature
{
    /**
     * @var ContactAndEventTransferController
     */
    protected $transferController;

    /**
     * @var string - Salesmanago confirmation subscription email id
     */
    protected $emailId;

    /**
     * @var ContactHelper
     */
    protected $contactHelper;

    /**
     * @return void
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->contactHelper = new ContactHelper();
        $this->initConf();
        $this->transferController = new ContactAndEventTransferController(Configuration::getInstance());
        $this->emailId = getenv('emailId');
        parent::setUp();
    }

    /**
     * Test of upsert contact with old useApiDoubleOptIn settings
     *
     * @return void
     * @throws Exception
     */
    public function testUpsertContactToSalesmanagoWithApiDoubleOptIntBasedOnThreeFields(): void
    {
        $ApiDoubleOptIn = new ApiDoubleOptIn();
        Configuration::getInstance()->setApiDoubleOptIn(
            $ApiDoubleOptIn
                ->setEnabled(true)
                ->setAccountId($this->faker->uuid)
                ->setTemplateId($this->faker->uuid)
        );

        $Contact = $this->contactHelper->generateContact();

        $Response = $this->transferController->transferContact($Contact);
        $this->assertNotEmpty($Response->getField('contactId'));
    }

    /**
     * Test Upset contact with apiDoubleOptInt with emilId and lang (newest option on sm api)
     *
     * @return void
     * @throws Exception
     */
    public function testUpsertContactToSalesmanagoWithApiDoubleOptIntBasedOnEmailId(): void
    {
        $ApiDoubleOptIn = new ApiDoubleOptIn();
        Configuration::getInstance()->setApiDoubleOptIn(
            $ApiDoubleOptIn
                ->setEnabled(true)
                ->setEmailId($this->emailId)
                ->setLang($this->faker->languageCode)
        );

        $Contact = $this->contactHelper->generateContact();

        $Response = $this->transferController->transferContact($Contact);
        $this->assertNotEmpty($Response->getField('contactId'));
    }
}