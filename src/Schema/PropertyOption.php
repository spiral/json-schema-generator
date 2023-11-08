<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;

/**
 * @internal
 */
final class PropertyOption
{
    /**
     * @param Type|class-string $value
     */
    public function __construct(
        public readonly Type|string $value,
    ) {
        if (\is_string($this->value) && !\class_exists($this->value)) {
            throw new InvalidTypeException('Invalid property option definition.');
        }
    }
}
