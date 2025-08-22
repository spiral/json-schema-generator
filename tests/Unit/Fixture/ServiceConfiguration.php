<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class ServiceEndpoint
{
    public function __construct(
        public string $url,
        public int $timeoutMs,
        public int $maxRetries,
        public bool $enabled,
    ) {}
}

final readonly class ServiceConfiguration
{
    public function __construct(
        public string $serviceName,
        public string $version,
        #[AdditionalProperties(valueType: 'object', valueClass: ServiceEndpoint::class)]
        #[Field(description: "Service endpoints configuration")]
        public array $endpoints = [],
        #[AdditionalProperties(valueType: 'int')]
        #[Field(description: "Feature flags and numeric settings")]
        public array $featureFlags = [],
        #[AdditionalProperties(valueType: 'string')]
        #[Field(description: "Environment variables")]
        public array $environment = [],
    ) {}
}
