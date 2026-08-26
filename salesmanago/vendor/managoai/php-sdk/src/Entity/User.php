<?php


namespace SALESmanago\Entity;


class User
{
    const
        USERNAME = 'username',
        PASSWORD = 'password',
        SHA1     = 'sha1',
        SHORT_ID = 'shortId';

    /**
     * @var string
     */
    private $email = null;

    /**
     * @var string
     */
    private $pass = null;

    /**
     * @param $email - string
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $pass - string
     * @return $this
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }
}