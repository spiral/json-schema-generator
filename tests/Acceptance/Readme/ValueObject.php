<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

final class ValueObject
{
    public function __construct(
        public readonly string $label,
        public readonly mixed $value,
    ) {}
}
