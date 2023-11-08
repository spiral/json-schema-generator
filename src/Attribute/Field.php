<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        public readonly string $title = '',
        public readonly string $description = '',
        public readonly mixed $default = null,
    ) {
    }
}
