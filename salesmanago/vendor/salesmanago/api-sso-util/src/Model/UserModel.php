<?php


namespace SALESmanago\Model;

use SALESmanago\Entity\User;
use SALESmanago\Helper\DataHelper;

class UserModel
{
    /**
     * @param User $User
     * @return array
     */
    public function getUserForAuthorization(User $User)
    {
        return DataHelper::filterDataArray([
            User::USERNAME => $User->getEmail(),
            User::PASSWORD => $User->getPass()
        ]);
    }
}