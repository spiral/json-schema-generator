<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Enum;

final class EnumTest extends TestCase
{
    public function testEnumWithStringValues(): void
    {
        $enum = new Enum(['active', 'inactive', 'pending']);

        $this->assertSame(['active', 'inactive', 'pending'], $enum->values);
    }

    public function testEnumWithIntegerValues(): void
    {
        $enum = new Enum([1, 2, 3, 5, 8]);

        $this->assertSame([1, 2, 3, 5, 8], $enum->values);
    }

    public function testEnumWithMixedValues(): void
    {
        $enum = new Enum(['draft', 1, 'published', 2]);

        $this->assertSame(['draft', 1, 'published', 2], $enum->values);
    }

    public function testEnumWithSingleValue(): void
    {
        $enum = new Enum(['only']);

        $this->assertSame(['only'], $enum->values);
    }

    public function testEnumWithEmptyArray(): void
    {
        $enum = new Enum([]);

        $this->assertSame([], $enum->values);
    }

    public function testEnumWithBooleanValues(): void
    {
        $enum = new Enum([true, false]);

        $this->assertSame([true, false], $enum->values);
    }

    public function testEnumWithFloatValues(): void
    {
        $enum = new Enum([1.5, 2.0, 3.14]);

        $this->assertSame([1.5, 2.0, 3.14], $enum->values);
    }

    public function testEnumWithNullValue(): void
    {
        $enum = new Enum([null, 'value']);

        $this->assertSame([null, 'value'], $enum->values);
    }
}
