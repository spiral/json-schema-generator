<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Pattern;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

final readonly class User
{
    public function __construct(
        #[Field(title: 'Full Name', description: 'User full name in Title Case')]
        #[Pattern('^[A-Z][a-z]+(?: [A-Z][a-z]+)*$')]
        #[Length(min: 2, max: 100)]
        public string $name,
        #[Field(title: 'Username')]
        #[Pattern('^[a-zA-Z0-9_]{3,20}$')]
        #[Length(min: 3, max: 20)]
        public string $username,
        #[Field(title: 'Email', format: Format::Email)]
        #[Pattern('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$')]
        public string $email,
    ) {}
}
