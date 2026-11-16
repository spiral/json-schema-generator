<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Testo\Assert;
use Testo\Test;

#[Test]
final class LengthTest
{
    public function testLengthWithBothMinAndMax(): void
    {
        $length = new Length(min: 5, max: 50);

        Assert::same($length->min, 5);
        Assert::same($length->max, 50);
    }

    public function testLengthWithOnlyMin(): void
    {
        $length = new Length(min: 10);

        Assert::same($length->min, 10);
        Assert::null($length->max);
    }

    public function testLengthWithOnlyMax(): void
    {
        $length = new Length(max: 100);

        Assert::null($length->min);
        Assert::same($length->max, 100);
    }

    public function testLengthWithoutParameters(): void
    {
        $length = new Length();

        Assert::null($length->min);
        Assert::null($length->max);
    }

    public function testLengthWithNamedArguments(): void
    {
        $length = new Length(max: 25, min: 3);

        Assert::same($length->min, 3);
        Assert::same($length->max, 25);
    }
}
