<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final readonly class Shelf
{
    /**
     * @param list<Book> $books
     */
    public function __construct(
        public Book $featured,
        public array $books = [],
    ) {}
}
