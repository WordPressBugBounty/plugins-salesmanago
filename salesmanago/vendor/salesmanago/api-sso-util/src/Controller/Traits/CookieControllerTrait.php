<?php


namespace SALESmanago\Controller\Traits;


use SALESmanago\Adapter\CookieManagerAdapter;

trait CookieControllerTrait
{
    /**
     * @var CookieManagerAdapter
     */
    protected $CookieManager;

    /**
     * @param CookieManagerAdapter $CookieManager
     * @return $this
     */
    public function setCookieManager(CookieManagerAdapter $CookieManager)
    {
        $this->CookieManager = $CookieManager;
        return $this;
    }

    /**
     * @return bool
     */
    private function checkIfCookieAdapterSet()
    {
        return (isset($this->CookieManager) && $this->CookieManager != null);
    }
}