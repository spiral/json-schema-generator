<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

enum Type: string
{
    case String = 'string';
    case Integer = 'integer';
    case Number = 'number';
    case Boolean = 'boolean';
    case Object = 'object';
    case Array = 'array';
    case Null = 'null';
    case Union = 'union';
    case Enum = 'enum';

    public static function fromBuiltIn(string $type): self
    {
        return match ($type) {
            'string' => self::String,
            'integer', 'int' => self::Integer,
            'float', 'double', 'number' => self::Number,
            'boolean', 'bool' => self::Boolean,
            'object' => self::Object,
            'array' => self::Array,
            'null' => self::Null,
            default => throw new \InvalidArgumentException(\sprintf('Invalid type `%s`.', $type)),
        };
    }
}
