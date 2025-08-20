<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class Range
{
    public function __construct(
        public int|float|null $min = null,
        public int|float|null $max = null,
        public ?bool $exclusiveMin = null,
        public ?bool $exclusiveMax = null,
    ) {}
}
