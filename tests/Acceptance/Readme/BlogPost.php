<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Enum;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Items;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class BlogPost
{
    public function __construct(
        #[Field(title: 'Tags', description: 'Post tags')]
        #[Items(min: 1, max: 10, unique: true)]
        public array $tags,
        #[Field(title: 'Categories', description: 'Post categories')]
        #[Length(min: 1, max: 5)]
        public array $categories,
        #[Field(title: 'Status')]
        #[Enum(['draft', 'published', 'archived', 'pending'])]
        public string $status,
        #[Field(title: 'Priority')]
        #[Enum([1, 2, 3, 4, 5])]
        public int $priority,
    ) {}
}
