<?php


namespace SALESmanago\Adapter;


interface SessionManagerAdapter
{
    /**
     * @param string $varName
     * @param null $sessionId
     * @return mixed
     */
    public function setToSession($varName, $sessionId = null);

    /**
     * @param string $varName
     * @return mixed
     */
    public function getFromSession($varName);

    /**
     * @param string $varName
     * @return mixed
     */
    public function deleteFromSession($varName);
}