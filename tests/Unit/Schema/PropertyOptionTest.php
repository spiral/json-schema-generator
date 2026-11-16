<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;
use Spiral\JsonSchemaGenerator\Schema\PropertyOption;
use Testo\Assert\ExpectException;
use Testo\Test;

#[Test]
final class PropertyOptionTest
{
    #[ExpectException(InvalidTypeException::class)]
    public function testInvalidValueException(): void
    {
        new PropertyOption(value: 'invalid');
    }
}
