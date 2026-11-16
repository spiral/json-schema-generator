<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;
use Testo\Assert;
use Testo\Test;

#[Test]
final class RangeTest
{
    public function testRangeWithIntegerBounds(): void
    {
        $range = new Range(min: 0, max: 100);

        Assert::same($range->min, 0);
        Assert::same($range->max, 100);
        Assert::null($range->exclusiveMin);
        Assert::null($range->exclusiveMax);
    }

    public function testRangeWithFloatBounds(): void
    {
        $range = new Range(min: 0.5, max: 99.9);

        Assert::same($range->min, 0.5);
        Assert::same($range->max, 99.9);
    }

    public function testRangeWithExclusiveBounds(): void
    {
        $range = new Range(min: 0, max: 100, exclusiveMin: true, exclusiveMax: true);

        Assert::same($range->min, 0);
        Assert::same($range->max, 100);
        Assert::true($range->exclusiveMin);
        Assert::true($range->exclusiveMax);
    }

    public function testRangeWithMixedExclusivity(): void
    {
        $range = new Range(min: 0, max: 100, exclusiveMin: true, exclusiveMax: false);

        Assert::true($range->exclusiveMin);
        Assert::false($range->exclusiveMax);
    }

    public function testRangeWithOnlyMin(): void
    {
        $range = new Range(min: 18);

        Assert::same($range->min, 18);
        Assert::null($range->max);
    }

    public function testRangeWithOnlyMax(): void
    {
        $range = new Range(max: 120);

        Assert::null($range->min);
        Assert::same($range->max, 120);
    }

    public function testRangeWithNegativeValues(): void
    {
        $range = new Range(min: -100, max: -10);

        Assert::same($range->min, -100);
        Assert::same($range->max, -10);
    }
}
