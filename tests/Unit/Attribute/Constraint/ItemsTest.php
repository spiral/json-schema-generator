<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Items;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ItemsTest
{
    public function testItemsWithAllConstraints(): void
    {
        $items = new Items(min: 2, max: 10, unique: true);

        Assert::same($items->min, 2);
        Assert::same($items->max, 10);
        Assert::true($items->unique);
    }

    public function testItemsWithOnlyMin(): void
    {
        $items = new Items(min: 1);

        Assert::same($items->min, 1);
        Assert::null($items->max);
        Assert::null($items->unique);
    }

    public function testItemsWithOnlyMax(): void
    {
        $items = new Items(max: 50);

        Assert::null($items->min);
        Assert::same($items->max, 50);
        Assert::null($items->unique);
    }

    public function testItemsWithOnlyUnique(): void
    {
        $items = new Items(unique: true);

        Assert::null($items->min);
        Assert::null($items->max);
        Assert::true($items->unique);
    }

    public function testItemsWithUniqueSetToFalse(): void
    {
        $items = new Items(unique: false);

        Assert::false($items->unique);
    }

    public function testItemsWithNoParameters(): void
    {
        $items = new Items();

        Assert::null($items->min);
        Assert::null($items->max);
        Assert::null($items->unique);
    }

    public function testItemsWithNamedArguments(): void
    {
        $items = new Items(unique: true, max: 5, min: 1);

        Assert::same($items->min, 1);
        Assert::same($items->max, 5);
        Assert::true($items->unique);
    }
}
