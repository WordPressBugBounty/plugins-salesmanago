<?php

namespace SALESmanago\Helper\Mapper\Map;

use JsonSerializable;
use SALESmanago\Helper\JsonDeserializable;

interface ItemEntityInterface extends JsonSerializable, JsonDeserializable
{
    /**
     * Set name
     *
     * @param string $name
     * @return EntityInterface
     */
    public function setName(?string $name): ItemEntityInterface;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Set value
     *
     * @param string $value
     * @return EntityInterface
     */
    public function setValue(?string $value): ItemEntityInterface;

    /**
     * Get value
     *
     * @return string
     */
    public function getValue(): ?string;

    /**
     * Set label
     *
     * @param string $label
     * @return EntityInterface
     */
    public function setLabel(?string $label): ItemEntityInterface;

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel(): ?string;
}