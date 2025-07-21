<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class FlexibleValue
{
    public function __construct(
        #[Field(title: 'Value', description: 'Can be either string or integer')]
        public readonly string|int $value,
        #[Field(title: 'Optional Flag', description: 'Boolean or null')]
        public readonly bool|null $flag = null,
        #[Field(title: 'Flexible Field', description: 'Can be string, int, or null')]
        public readonly string|int|null $flex = null,
    ) {}
}
