<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final readonly class NullableCollection
{
    public function __construct(
        /** @var array<string> */
        public ?array $tags = null,
    ) {}
}
