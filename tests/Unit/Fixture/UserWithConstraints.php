<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Enum;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Items;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\MultipleOf;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Pattern;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

final readonly class UserWithConstraints
{
    public function __construct(
        #[Field(title: 'Full Name', description: 'User full name in Title Case')]
        #[Pattern('^[A-Z][a-z]+(?: [A-Z][a-z]+)*$')]
        #[Length(min: 2, max: 100)]
        public string $name,
        #[Field(title: 'Email', format: Format::Email)]
        #[Pattern('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$')]
        public string $email,
        #[Field(title: 'Age', description: 'User age in years')]
        #[Range(min: 13, max: 120)]
        public int $age,
        #[Field(title: 'Price', description: 'Product price in USD')]
        #[Range(min: 0.01, max: 99999.99)]
        #[MultipleOf(0.01)]
        public float $price,
        #[Field(title: 'Tags', description: 'User tags')]
        #[Items(min: 1, max: 10, unique: true)]
        public array $tags,
        #[Field(title: 'Status')]
        #[Enum(['active', 'inactive', 'suspended'])]
        public string $status,
        #[Field(title: 'Username')]
        #[Pattern('^[a-zA-Z0-9_]{3,20}$')]
        #[Length(min: 3, max: 20)]
        public string $username,
    ) {}
}
