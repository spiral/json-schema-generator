<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class Items
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
        public ?bool $unique = null,
    ) {}
}
