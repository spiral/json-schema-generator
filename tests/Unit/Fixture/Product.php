<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class Product
{
    public function __construct(
        public string $id,
        public string $name,
        #[Range(min: 0)]
        public float $price,
        #[AdditionalProperties(valueType: 'mixed')]
        #[Field(description: "Product-specific attributes that vary by category")]
        public array $attributes = [],
        #[AdditionalProperties(valueType: 'string')]
        #[Field(description: "SEO and marketing metadata")]
        public array $metadata = [],
    ) {}
}
