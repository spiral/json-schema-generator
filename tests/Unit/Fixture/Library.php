<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final readonly class Library
{
    public function __construct(
        public Shelf $shelf,
    ) {}
}
