<?php

namespace App\Helper;

use App\Entity\Field;
use App\Entity\Item;
use App\Enum\FieldTypeEnum;
use Exception;

abstract class ItemHelper
{
    /**
     * @param array $fields
     * @param Item|null $item
     * @param bool|null $toJson
     * @return array
     * @throws Exception
     */
    static function formatValues(array $fields, ?Item $item, ?bool $toJson = false): array
    {
        return array_reduce(
            $fields,
            function (array $current, Field $field) use ($item, $toJson) {
                $current[$field->getName()] = ItemHelper::formatValueWithDefault($field, $item, $toJson);
                return $current;
            },
            [],
        );
    }

    /**
     * @throws Exception
     */
    static function formatValueWithDefault(Field $field, ?Item $item, ?bool $toJson = false): mixed
    {
        $value = ItemHelper::defaultValue($field, $item, $toJson);

        if ($value === null) {
            return null;
        }

        return ItemHelper::formatValue($field, $value, $toJson);
    }

    static function defaultValue(Field $field, ?Item $item, ?bool $toJson = false): mixed
    {
        $value = ItemHelper::getValue($field, $item, $toJson);
        if ($value === null) {
            $value = $field->getDefaultValue();

            if ($value === null) {
                return null;
            }
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    static function getValue(Field $field, ?Item $item, ?bool $toJson = false): mixed
    {
        $values = $item->getValues();
        $key = $field->getKey();
        $name = $field->getName();
        $value = isset($values[$name])
            ? $values[$name]
            : (isset($values[$key])
                ? $values[$key]
                : null);

        return ItemHelper::formatValue($field, $value, $toJson);
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
        if ($value === null) {
            return null;
        }

        return match ($type) {
            FieldTypeEnum::Boolean => TypeHelper::boolToString($value),
            FieldTypeEnum::Datetime => TypeHelper::dateToString($value),
            FieldTypeEnum::ArrayString => TypeHelper::arrayToString($value, 'string'),
            FieldTypeEnum::ArrayInt => TypeHelper::arrayToString($value, 'int'),
            FieldTypeEnum::ArrayFloat => TypeHelper::arrayToString($value, 'float'),
            default => (string)$value
        };
    }

    /**
     * @param array $values
     * @param Field[] $fields
     * @return array
     */
    static function filterValues(array $values, array $fields): array
    {
        $keys = array_map(fn(Field $field) => $field->getName(), $fields);

        return array_filter(
            $values,
            fn($key) => in_array($key, $keys) || ($key === 'id' || $key === 'name'),
            ARRAY_FILTER_USE_KEY
        );
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