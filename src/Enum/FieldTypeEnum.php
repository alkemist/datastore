<?php

namespace App\Enum;

use Traversable;

enum FieldTypeEnum: string
{
    case String = 'string';
    case Int = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';
    case Datetime = 'datetime';
    case Json = 'json';

    public static function choices(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }

    public function toString(): string
    {
        return match ($this) {
            self::String => 'String',
            self::Int => 'Integer',
            self::Float => 'Float',
            self::Boolean => 'Boolean',
            self::Datetime => 'Datetime',
            self::Json => 'Json',
        };
    }

    public static function values(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->toString() => $case->value;
        }
    }
}