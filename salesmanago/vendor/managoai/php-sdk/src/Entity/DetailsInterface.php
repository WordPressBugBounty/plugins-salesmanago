<?php

namespace SALESmanago\Entity;

use \JsonSerializable;

interface DetailsInterface extends JsonSerializable
{
    /**
     * @param mixed $value
     * @param int|null $index
     * @return DetailsInterface
     */
    public function set($value, $index = null);

    /**
     * @param int|null $index
     * @return int|null
     */
    public function get($index = null);

    /**
     * @param int|null $index
     * @return DetailsInterface
     */
    public function unset($index = null);

    /**
     * @return DetailsInterface
     */
    public function clear();
}