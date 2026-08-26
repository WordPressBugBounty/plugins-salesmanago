<?php

namespace SALESmanago\Helper\Mapper\Map;

use SALESmanago\Helper\Mapper\Map\ItemEntityInterface;

class ItemEntity implements ItemEntityInterface
{
    /**
     * @var string
     */
    protected ?string $name;

    /**
     * @var string
     */
    protected ?string $value;

    /**
     * @var string
     */
    protected ?string $label;

    /**
     * @inheritDoc
     */
    public function setName(?string $name): ItemEntityInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setValue(?string $value): ItemEntityInterface
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setLabel(?string $label): ItemEntityInterface
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'name'  => $this->name ?? null,
            'value' => $this->value ?? null,
            'label' => $this->label ?? null,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function jsonDeserialize(string $json): self
    {
        $data = json_decode($json, true);

        $item = new self();

        $item->setName($data['name']);
        $item->setValue($data['value']);
        $item->setLabel($data['label']);

        return $item;
    }
}
