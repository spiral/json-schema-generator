<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;
use Spiral\JsonSchemaGenerator\Schema\PropertyOption;

final class PropertyOptionTest extends TestCase
{
    public function testInvalidValueException(): void
    {
        $this->expectException(InvalidTypeException::class);
        new PropertyOption(value: 'invalid');
    }
}
