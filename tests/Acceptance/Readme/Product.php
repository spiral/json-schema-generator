<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\MultipleOf;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class Product
{
    public function __construct(
        #[Field(title: 'Price', description: 'Product price in USD')]
        #[Range(min: 0.01, max: 99999.99)]
        #[MultipleOf(0.01)]
        public float $price,
        #[Field(title: 'Stock Quantity')]
        #[Range(min: 0, max: 10000)]
        public int $stock,
        #[Field(title: 'Discount Percentage')]
        #[Range(min: 0, max: 100, exclusiveMax: true)]
        public float $discountPercent,
    ) {}
}
