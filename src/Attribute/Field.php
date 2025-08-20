<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute;

use Spiral\JsonSchemaGenerator\Schema\Format;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Field
{
    public function __construct(
        public string $title = '',
        public string $description = '',
        public mixed $default = null,
        public ?Format $format = null,
    ) {}
}
