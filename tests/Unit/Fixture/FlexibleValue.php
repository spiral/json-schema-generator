<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class FlexibleValue
{
    public function __construct(
        #[Field(title: 'Value', description: 'Can be either string or integer')]
        public string|int $value,
        #[Field(title: 'Optional Flag', description: 'Boolean or null')]
        public bool|null $flag = null,
        #[Field(title: 'Flexible Field', description: 'Can be string, int, or null')]
        public string|int|null $flex = null,
    ) {}
}
