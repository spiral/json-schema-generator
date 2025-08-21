<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
        #[AdditionalProperties(valueType: 'mixed')]
        #[Field(description: "Custom user attributes and metadata")]
        public array $attributes = [],
        #[AdditionalProperties(valueType: 'string')]
        #[Field(description: "User preferences")]
        public array $preferences = [],
    ) {}
}
