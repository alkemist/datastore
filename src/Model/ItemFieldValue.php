<?php

namespace App\Model;

use App\Entity\Field;
use App\Enum\FieldTypeEnum;
use App\Helper\ItemHelper;

class ItemFieldValue
{
    private bool $valueChanged = false;

    private bool $isDefaultValue = false;

    public function __construct(
        private readonly Field $field,
        private mixed          $value,
        bool                   $isNull,
    ) {
        $this->isDefaultValue = $isNull;
    }

    public function getDefaultValue()
    {
        return $this->field->getDefaultValue();
    }

    public function getIsDefaultValue(): bool
    {
        return $this->isDefaultValue;
    }

    /**
     * @throws \Exception
     */
    public function setIsDefaultValue(bool $isDefaultValue): void
    {
        if ($isDefaultValue) {
            if (!$this->valueChanged) {
                $this->value = null;
            }
            $this->isDefaultValue = true;
        }

        if (!$isDefaultValue) {
            $this->value = ItemHelper::formatValue($this->field, null);
        }
    }

    /**
     * @throws \Exception
     */
    public function getValue()
    {
        if (!$this->isDefaultValue) {
            return ItemHelper::formatValue($this->field, $this->value);
        }

        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->valueChanged = true;
        $this->value = $value;
    }

    public function getType(): FieldTypeEnum
    {
        return $this->getField()->getType();
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function __toString(): string
    {
        return $this->field->getKey();
    }
}