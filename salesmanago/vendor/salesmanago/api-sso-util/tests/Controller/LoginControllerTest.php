<?php


namespace Tests\Controller;


use PHPUnit\Framework\TestCase;
use SALESmanago\Controller\LoginController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;

class LoginControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testLoginSuccess()
    {
        $conf = Configuration::getInstance();
        $User = new User();
        $loginController = new LoginController($conf);

        $User = $User
            ->setEmail('semowet930@boldhut.com')
            ->setPass('#Salesmanago123');

        $Response = $loginController->login($User);

        $this->assertInstanceOf('SALESmanago\Entity\Response', $Response);
        $this->assertEquals(true, $Response->getStatus());
    }

    /**
     * @throws Exception
     */
    public function testLoginAuthorizationFail()
    {
        $this->expectException('SALESmanago\Exception\Exception');

        $conf = Configuration::getInstance();
        $User = new User();
        $loginController = new LoginController($conf);

        $User = $User
            ->setEmail('notright@mail.com')
            ->setPass('test123#123');

        $loginController->login($User);
    }
}