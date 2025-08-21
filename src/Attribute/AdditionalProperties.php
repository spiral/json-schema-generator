<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class AdditionalProperties
{
    /**
     * @param string $valueType The type of values for additional properties (e.g., 'string', 'int', 'number', 'boolean', 'mixed')
     * @param class-string|null $valueClass Optional class reference for object-typed additional properties
     */
    public function __construct(
        public string $valueType,
        public ?string $valueClass = null,
    ) {}
}
