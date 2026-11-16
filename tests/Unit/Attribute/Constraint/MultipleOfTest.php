<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\MultipleOf;
use Testo\Assert;
use Testo\Test;

#[Test]
final class MultipleOfTest
{
    public function testMultipleOfWithInteger(): void
    {
        $multipleOf = new MultipleOf(5);

        Assert::same($multipleOf->value, 5);
    }

    public function testMultipleOfWithFloat(): void
    {
        $multipleOf = new MultipleOf(0.01);

        Assert::same($multipleOf->value, 0.01);
    }

    public function testMultipleOfWithLargeNumber(): void
    {
        $multipleOf = new MultipleOf(1000000);

        Assert::same($multipleOf->value, 1000000);
    }

    public function testMultipleOfWithDecimal(): void
    {
        $multipleOf = new MultipleOf(2.5);

        Assert::same($multipleOf->value, 2.5);
    }

    public function testMultipleOfWithSmallDecimal(): void
    {
        $multipleOf = new MultipleOf(0.001);

        Assert::same($multipleOf->value, 0.001);
    }
}
