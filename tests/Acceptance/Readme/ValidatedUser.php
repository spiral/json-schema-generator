<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class ValidatedUser
{
    public function __construct(
        #[Field(title: 'Name', description: 'User full name')]
        /** @var non-empty-string */
        public readonly string $name,
        #[Field(title: 'Age', description: 'User age')]
        /** @var positive-int */
        public readonly int $age,
        #[Field(title: 'Score', description: 'User score between 0 and 100')]
        /** @var int<0, 100> */
        public readonly int $score,
        #[Field(title: 'Email', description: 'User email address')]
        /** @var non-empty-string */
        public readonly string $email,
        #[Field(title: 'Phone Number', description: 'Numeric phone number')]
        /** @var numeric-string */
        public readonly string $phone,
        #[Field(title: 'Tags', description: 'User tags')]
        /** @var non-empty-array<string> */
        public readonly array $tags = [],
        #[Field(title: 'Preferences', description: 'User preferences')]
        /** @var array{theme: string, notifications: bool} */
        public readonly array $preferences = [],
    ) {}
}
