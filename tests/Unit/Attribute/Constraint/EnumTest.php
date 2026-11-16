<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Enum;
use Testo\Assert;
use Testo\Test;

#[Test]
final class EnumTest
{
    public function testEnumWithStringValues(): void
    {
        $enum = new Enum(['active', 'inactive', 'pending']);

        Assert::same($enum->values, ['active', 'inactive', 'pending']);
    }

    public function testEnumWithIntegerValues(): void
    {
        $enum = new Enum([1, 2, 3, 5, 8]);

        Assert::same($enum->values, [1, 2, 3, 5, 8]);
    }

    public function testEnumWithMixedValues(): void
    {
        $enum = new Enum(['draft', 1, 'published', 2]);

        Assert::same($enum->values, ['draft', 1, 'published', 2]);
    }

    public function testEnumWithSingleValue(): void
    {
        $enum = new Enum(['only']);

        Assert::same($enum->values, ['only']);
    }

    public function testEnumWithEmptyArray(): void
    {
        $enum = new Enum([]);

        Assert::same($enum->values, []);
    }

    public function testEnumWithBooleanValues(): void
    {
        $enum = new Enum([true, false]);

        Assert::same($enum->values, [true, false]);
    }

    public function testEnumWithFloatValues(): void
    {
        $enum = new Enum([1.5, 2.0, 3.14]);

        Assert::same($enum->values, [1.5, 2.0, 3.14]);
    }

    public function testEnumWithNullValue(): void
    {
        $enum = new Enum([null, 'value']);

        Assert::same($enum->values, [null, 'value']);
    }
}
