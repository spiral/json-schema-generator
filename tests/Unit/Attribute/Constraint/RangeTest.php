<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;

final class RangeTest extends TestCase
{
    public function testRangeWithIntegerBounds(): void
    {
        $range = new Range(min: 0, max: 100);

        $this->assertSame(0, $range->min);
        $this->assertSame(100, $range->max);
        $this->assertNull($range->exclusiveMin);
        $this->assertNull($range->exclusiveMax);
    }

    public function testRangeWithFloatBounds(): void
    {
        $range = new Range(min: 0.5, max: 99.9);

        $this->assertSame(0.5, $range->min);
        $this->assertSame(99.9, $range->max);
    }

    public function testRangeWithExclusiveBounds(): void
    {
        $range = new Range(min: 0, max: 100, exclusiveMin: true, exclusiveMax: true);

        $this->assertSame(0, $range->min);
        $this->assertSame(100, $range->max);
        $this->assertTrue($range->exclusiveMin);
        $this->assertTrue($range->exclusiveMax);
    }

    public function testRangeWithMixedExclusivity(): void
    {
        $range = new Range(min: 0, max: 100, exclusiveMin: true, exclusiveMax: false);

        $this->assertTrue($range->exclusiveMin);
        $this->assertFalse($range->exclusiveMax);
    }

    public function testRangeWithOnlyMin(): void
    {
        $range = new Range(min: 18);

        $this->assertSame(18, $range->min);
        $this->assertNull($range->max);
    }

    public function testRangeWithOnlyMax(): void
    {
        $range = new Range(max: 120);

        $this->assertNull($range->min);
        $this->assertSame(120, $range->max);
    }

    public function testRangeWithNegativeValues(): void
    {
        $range = new Range(min: -100, max: -10);

        $this->assertSame(-100, $range->min);
        $this->assertSame(-10, $range->max);
    }
}
