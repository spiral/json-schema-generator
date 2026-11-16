<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final class DynamicConfig
{
    public function __construct(
        #[Field(title: 'Config Name', description: 'Name of the configuration set')]
        public readonly string $name,
        #[Field(title: 'Version', description: 'Configuration version')]
        public readonly int $version,

        /**
         * Dynamic settings map that can contain any string values
         */
        #[Field(title: 'Settings', description: 'Dynamic configuration settings')]
        #[AdditionalProperties(valueType: 'string')]
        public readonly array $settings = [],

        /**
         * Dynamic metadata with nested ValueObject instances
         */
        #[Field(title: 'Metadata', description: 'Dynamic configuration metadata')]
        #[AdditionalProperties(valueType: 'object', valueClass: ValueObject::class)]
        public readonly array $metadata = [],
    ) {}
}
