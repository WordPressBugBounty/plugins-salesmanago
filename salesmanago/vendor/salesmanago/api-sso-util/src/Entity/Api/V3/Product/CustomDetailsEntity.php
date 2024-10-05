<?php

namespace SALESmanago\Entity\Api\V3\Product;

use SALESmanago\Entity\DetailsInterface;

class CustomDetailsEntity implements DetailsInterface
{
    /**
     * @var array
     */
    protected $details = [];

    /**
     * @inheritDoc
     */
    public function set($value, $index = null)
    {
        if ($index != null) {
            $this->details[$index] = $value;
        } else {
            $this->details[] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get($index = null)
    {
        return $this->details[$index] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function unset($index = null)
    {
        if (!isset($this->details[$index])) {
           return $this;
        }

        unset($this->details[$index]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->details = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $response = [];

        foreach ($this->details as $key => $detail) {

            if ($detail === null || $detail === '') {
                continue;
            }

            $response['detail' . $key] = $detail;
        }

        return $response;
    }
}