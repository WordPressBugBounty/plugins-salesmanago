<?php


namespace SALESmanago\Controller\Traits;


use SALESmanago\Adapter\SessionManagerAdapter;

trait SessionControllerTrait
{
    /**
     * @var SessionManagerAdapter
     */
    protected $SessionManager;

    /**
     * @param SessionManagerAdapter $SessionManager
     * @return $this
     */
    public function setSessionManager(SessionManagerAdapter $SessionManager)
    {
        $this->SessionManager = $SessionManager;
        return $this;
    }

    /**
     * @return bool
     */
    private function checkIfSessionAdapterSet()
    {
        return (isset($this->SessionManager) && $this->SessionManager != null);
    }
}