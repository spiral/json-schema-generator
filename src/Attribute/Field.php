<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute;

use Spiral\JsonSchemaGenerator\Schema\Format;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        public readonly string $title = '',
        public readonly string $description = '',
        public readonly mixed $default = null,
        public readonly ?Format $format = null,
    ) {}
}
