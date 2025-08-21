<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class DatabaseConfig
{
    public function __construct(
        #[Field(description: "Database host")]
        public string $host,
        #[Field(description: "Database port")]
        public int $port,
        public string $username,
        public string $password,
        #[AdditionalProperties(valueType: 'string')]
        #[Field(description: "Driver-specific configuration options")]
        public array $driverOptions = [],
    ) {}
}
