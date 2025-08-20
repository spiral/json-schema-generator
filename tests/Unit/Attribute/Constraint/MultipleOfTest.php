<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\MultipleOf;

final class MultipleOfTest extends TestCase
{
    public function testMultipleOfWithInteger(): void
    {
        $multipleOf = new MultipleOf(5);

        $this->assertSame(5, $multipleOf->value);
    }

    public function testMultipleOfWithFloat(): void
    {
        $multipleOf = new MultipleOf(0.01);

        $this->assertSame(0.01, $multipleOf->value);
    }

    public function testMultipleOfWithLargeNumber(): void
    {
        $multipleOf = new MultipleOf(1000000);

        $this->assertSame(1000000, $multipleOf->value);
    }

    public function testMultipleOfWithDecimal(): void
    {
        $multipleOf = new MultipleOf(2.5);

        $this->assertSame(2.5, $multipleOf->value);
    }

    public function testMultipleOfWithSmallDecimal(): void
    {
        $multipleOf = new MultipleOf(0.001);

        $this->assertSame(0.001, $multipleOf->value);
    }
}
