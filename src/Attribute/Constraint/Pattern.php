<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Pattern
{
    public function __construct(
        public string $pattern,
    ) {}
}
