<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final readonly class MixedValue
{
    public function __construct(
        public mixed $value,
    ) {}
}
