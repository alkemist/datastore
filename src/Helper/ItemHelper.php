<?php

namespace App\Helper;

use App\Entity\Field;
use App\Entity\Item;
use App\Enum\FieldTypeEnum;
use Exception;

abstract class ItemHelper
{
    /**
     * @param Field[] $fields
     * @param Item|null $item
     * @return array
     * @throws Exception
     */
    static function formatValues(array $fields, ?Item $item, ?bool $toJson): array
    {
        return array_reduce(
            $fields,
            function (array $current, Field $field) use ($item, $toJson) {
                $current[$field->getKey()] = ItemHelper::formatValueWithDefault($field, $item, $toJson);
                return $current;
            },
            [],
        );
    }

    /**
     * @throws Exception
     */
    static function formatValueWithDefault(Field $field, ?Item $item, ?bool $toJson = false)
    {
        $value = ItemHelper::defaultValue($field, $item);

        if ($value === null) {
            return null;
        }

        return ItemHelper::formatValue($field, $value, $toJson);
    }

    static function defaultValue(Field $field, ?Item $item)
    {
        $value = ItemHelper::getValue($field, $item);
        if ($value === null) {
            $value = $field->getDefaultValue();

            if ($value === null) {
                return null;
            }
        }

        return $value;
    }

    static function getValue(Field $field, ?Item $item)
    {
        return $item->getValues()[$field->getKey()] ?? null;
    }

    /**
     * @throws Exception
     */
    static function formatValue(Field $field, mixed $value, ?bool $toJson = false)
    {
        return match ($field->getType()) {
            FieldTypeEnum::String => TypeHelper::toString($value),
            FieldTypeEnum::Int => TypeHelper::toInt($value),
            FieldTypeEnum::Float => TypeHelper::toFloat($value),
            FieldTypeEnum::Boolean => TypeHelper::toBool($value),
            FieldTypeEnum::Datetime => TypeHelper::toDate($value, $toJson),
            default => $value
        };
    }

    static function toString(mixed $value, FieldTypeEnum $type): string|null
    {
        dump($value);
        if ($value === null) {
            return null;
        }

        return match ($type) {
            FieldTypeEnum::Boolean => TypeHelper::boolToString($value),
            FieldTypeEnum::Datetime => TypeHelper::dateToString($value),
            default => (string)$value
        };
    }

    /**
     * @param Field[] $fields
     * @param Item|null $item
     * @return array
     */
    static function defaultValues(array $fields, ?Item $item): array
    {
        return array_reduce(
            $fields,
            function (array $current, Field $field) use ($item) {
                $current[$field->getKey()] = ItemHelper::defaultValue($field, $item);
                return $current;
            },
            [],
        );
    }
}