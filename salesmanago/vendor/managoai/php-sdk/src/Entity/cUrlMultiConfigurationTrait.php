<?php

namespace SALESmanago\Entity;

trait cUrlMultiConfigurationTrait
{

    /**
     * @var bool
     */
    protected bool $cUrlMulti = false;

    /**
     * Sets the cURL multi option.
     *
     * @param bool $cUrlMulti
     * @return $this
     */
    public function setCurlMulti($cUrlMulti)
    {
        $this->cUrlMulti = $cUrlMulti;
        return $this;
    }

    /**
     * Returns the cURL multi option.
     *
     * @return bool
     */
    public function getCurlMulti(): bool
    {
        return $this->cUrlMulti;
    }
}
