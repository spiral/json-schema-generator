<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;

final class LengthTest extends TestCase
{
    public function testLengthWithBothMinAndMax(): void
    {
        $length = new Length(min: 5, max: 50);

        $this->assertSame(5, $length->min);
        $this->assertSame(50, $length->max);
    }

    public function testLengthWithOnlyMin(): void
    {
        $length = new Length(min: 10);

        $this->assertSame(10, $length->min);
        $this->assertNull($length->max);
    }

    public function testLengthWithOnlyMax(): void
    {
        $length = new Length(max: 100);

        $this->assertNull($length->min);
        $this->assertSame(100, $length->max);
    }

    public function testLengthWithoutParameters(): void
    {
        $length = new Length();

        $this->assertNull($length->min);
        $this->assertNull($length->max);
    }

    public function testLengthWithNamedArguments(): void
    {
        $length = new Length(max: 25, min: 3);

        $this->assertSame(3, $length->min);
        $this->assertSame(25, $length->max);
    }
}
