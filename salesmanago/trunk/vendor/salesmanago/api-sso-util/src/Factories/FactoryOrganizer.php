<?php

namespace SALESmanago\Factories;


use SALESmanago\Controller\ContactAndEventTransferController;
use SALESmanago\Controller\LoginController;
use SALESmanago\Controller\UserController;

class FactoryOrganizer
{
    const
        LOGIN_C = 'LoginC',
        USER_C = 'UserC',
        CONTACT_AND_EVENT_TRANSFER_C = 'ContactAndEventTransferC';



    public function getInst($const, $param)
    {
        switch ($const) {
            case self::LOGIN_C:
                return new LoginController($param);
            case self::USER_C:
                return new UserController($param);
            case self::CONTACT_AND_EVENT_TRANSFER_C:
                return new ContactAndEventTransferController($param);
            default:
                return false;
            break;
        }
    }
}
