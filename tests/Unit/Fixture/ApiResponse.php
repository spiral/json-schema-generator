<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class ApiResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $statusCode,
        #[AdditionalProperties(valueType: 'mixed')]
        #[Field(description: "Dynamic response data")]
        public array $data = [],
        #[AdditionalProperties(valueType: 'string')]
        #[Field(description: "Response headers")]
        public array $headers = [],
    ) {}
}
