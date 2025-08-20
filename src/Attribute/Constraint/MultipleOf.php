<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class MultipleOf
{
    public function __construct(
        public int|float $value,
    ) {}
}
