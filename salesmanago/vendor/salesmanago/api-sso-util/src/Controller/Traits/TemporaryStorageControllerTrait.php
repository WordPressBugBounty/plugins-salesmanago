<?php


namespace SALESmanago\Controller\Traits;


use SALESmanago\Entity\Configuration;
use SALESmanago\Controller\Traits\CookieControllerTrait;
use SALESmanago\Controller\Traits\SessionControllerTrait;
use SALESmanago\Adapter\CookieManagerAdapter;
use SALESmanago\Adapter\SessionManagerAdapter;

trait TemporaryStorageControllerTrait
{
    use CookieControllerTrait;
    use SessionControllerTrait;

    /**
     * @var Configuration
     */
    protected $conf;

    /**
     * @param string $id
     * @return bool
     */
    public function setSmEvent($id)
    {
        if($this->checkIfCookieAdapterSet()) {
           $this->CookieManager->setCookie(
               CookieManagerAdapter::EVENT_COOKIE,
               $id,
               time()+$this->conf->getEventCookieTtl()
           );
        }

        if ($this->checkIfSessionAdapterSet()) {
            $this->SessionManager->setToSession(CookieManagerAdapter::EVENT_COOKIE, $id);
        }

        return true;
    }

    /**
     * @return null
     */
    public function getSmEvent()
    {
        $id = null;
        if($this->checkIfCookieAdapterSet()) {
            $id = $this->CookieManager->getCookie(CookieManagerAdapter::EVENT_COOKIE);
        }

        if ($this->checkIfSessionAdapterSet()) {
            $id = !empty($id)
                ? $id
                : $this->SessionManager->getFromSession(CookieManagerAdapter::EVENT_COOKIE);
        }

        return $id;
    }

    /**
     * @return bool
     */
    public function unsetSmEvent()
    {
        if($this->checkIfCookieAdapterSet()) {
            $this->CookieManager->deleteCookie(CookieManagerAdapter::EVENT_COOKIE);
        }

        if ($this->checkIfSessionAdapterSet()) {
            $this->SessionManager->deleteFromSession(CookieManagerAdapter::EVENT_COOKIE);
        }

        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function setSmClient($id)
    {
        if($this->checkIfCookieAdapterSet()) {
            $this->CookieManager->setCookie(
                CookieManagerAdapter::CLIENT_COOKIE,
                $id,
                time()+$this->conf->getContactCookieTtl()
            );
        }

        if ($this->checkIfSessionAdapterSet()) {
            $this->SessionManager->setToSession(CookieManagerAdapter::CLIENT_COOKIE, $id);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getSmClient()
    {
        $id = null;
        if($this->checkIfCookieAdapterSet()) {
            $id = $this->CookieManager->getCookie(CookieManagerAdapter::CLIENT_COOKIE);
        }

        if ($this->checkIfSessionAdapterSet()) {
            $id = !empty($id)
                ? $id
                : $this->SessionManager->getFromSession(CookieManagerAdapter::CLIENT_COOKIE);
        }

        return $id;
    }

    /**
     * @return bool
     */
    public function unsetSmClient()
    {
        if($this->checkIfCookieAdapterSet()) {
            $this->CookieManager->deleteCookie(CookieManagerAdapter::CLIENT_COOKIE);
        }

        if ($this->checkIfSessionAdapterSet()) {
            $this->SessionManager->deleteFromSession(CookieManagerAdapter::CLIENT_COOKIE);
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkIfAdaptersSet(){
        if(!$this->checkIfSessionAdapterSet()
            && !$this->checkIfCookieAdapterSet()
        ) {
            return false;
        }

        return true;
    }
}