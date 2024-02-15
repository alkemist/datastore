<?php

namespace App\Helper;

use DateTime;
use DateTimeInterface;
use Exception;

abstract class TypeHelper
{
    static function toBool(mixed $value): bool|null
    {
        if ($value === null) {
            return null;
        }

        return is_bool($value)
            ? $value
            : (is_string($value)
                ? $value === 'true'
                : boolval($value));
    }

    static function boolToString(bool $value): string
    {
        return $value ? "true" : "false";
    }

    static function toInt(mixed $value): int
    {
        return intval($value);
    }

    static function toFloat(mixed $value): float
    {
        return floatval($value);
    }

    /**
     * @throws Exception
     */
    static function toDate(string|DateTimeInterface|null $value, ?bool $toJson): DateTimeInterface|string|null
    {
        if (empty($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        $date = new DateTime($value);

        return $toJson
            ? $date->format(DateTimeInterface::ATOM)
            : $date;
    }

    public static function dateToString(DateTimeInterface $value): string
    {
        return $value->format(DateTimeInterface::ATOM);
    }

    public static function toString(mixed $value): string
    {
        if ($value === null) {
            return "";
        }

        return (string)$value;
    }
}