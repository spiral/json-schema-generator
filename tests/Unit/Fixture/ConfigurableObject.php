<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class ConfigurableObject
{
    public function __construct(
        public string $name,
        public int $version,
        #[AdditionalProperties(valueType: 'string')]
        #[Field(description: "String-based configuration options")]
        public array $stringOptions = [],
        #[AdditionalProperties(valueType: 'int')]
        #[Field(description: "Numeric configuration values")]
        public array $numericSettings = [],
        #[AdditionalProperties(valueType: 'mixed')]
        #[Field(description: "Dynamic data of any type")]
        public array $dynamicData = [],
    ) {}
}
