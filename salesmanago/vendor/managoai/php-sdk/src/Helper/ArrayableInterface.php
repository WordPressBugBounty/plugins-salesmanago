<?php

namespace SALESmanago\Helper;

interface ArrayableInterface
{
    /**
     * @return array form specific object
     */
    public function toArray(): array;
}