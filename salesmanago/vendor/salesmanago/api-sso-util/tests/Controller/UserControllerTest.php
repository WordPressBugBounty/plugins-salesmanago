<?php


namespace Tests\Controller;


use PHPUnit\Framework\TestCase;
use SALESmanago\Controller\LoginController;
use SALESmanago\Controller\UserController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;

class UserControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetOwnerList()
    {
        $User = new User();
        $LoginController = new LoginController(Configuration::getInstance());

        $User = $User
            ->setEmail('semowet930@boldhut.com')
            ->setPass('#Salesmanago123');

        $LoginController->login($User);

        $UserController = new UserController(Configuration::getInstance());
        $arrayResponse = $UserController->getOwnersList();

        $this->assertIsArray($arrayResponse);
        $this->assertNotEmpty($arrayResponse);
    }
}