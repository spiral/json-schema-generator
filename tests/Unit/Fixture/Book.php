<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final readonly class Book
{
    public function __construct(
        public string $title,
        public Priority $priority = Priority::Low,
    ) {}
}
