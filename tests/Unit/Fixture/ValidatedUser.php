<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class ValidatedUser
{
    public function __construct(
        #[Field(title: 'Name', description: 'User full name')]
        /** @var non-empty-string */
        public string $name,
        #[Field(title: 'Age', description: 'User age')]
        /** @var positive-int */
        public int $age,
        #[Field(title: 'Score', description: 'User score between 0 and 100')]
        /** @var int<0, 100> */
        public int $score,
        #[Field(title: 'Email', description: 'User email address')]
        /** @var non-empty-string */
        public string $email,
        #[Field(title: 'Phone Number', description: 'Numeric phone number')]
        /** @var numeric-string */
        public string $phone,
        #[Field(title: 'Tags', description: 'User tags')]
        /** @var non-empty-array<string> */
        public array $tags = [],
        #[Field(title: 'Preferences', description: 'User preferences')]
        /** @var array{theme: string, notifications: bool} */
        public array $preferences = [],
    ) {}
}
