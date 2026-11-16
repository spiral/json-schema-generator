<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

final class ContactInfo
{
    public function __construct(
        #[Field(title: 'Email', description: 'User email address', format: Format::Email)]
        public readonly string $email,
        #[Field(title: 'Website', description: 'Personal website', format: Format::Uri)]
        public readonly ?string $website = null,
        #[Field(title: 'Birth Date', description: 'Date of birth', format: Format::Date)]
        public readonly ?string $birthDate = null,
        #[Field(title: 'Last Login', description: 'Last login timestamp', format: Format::DateTime)]
        public readonly ?string $lastLogin = null,
    ) {}
}
