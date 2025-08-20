<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Items;

final class ItemsTest extends TestCase
{
    public function testItemsWithAllConstraints(): void
    {
        $items = new Items(min: 2, max: 10, unique: true);

        $this->assertSame(2, $items->min);
        $this->assertSame(10, $items->max);
        $this->assertTrue($items->unique);
    }

    public function testItemsWithOnlyMin(): void
    {
        $items = new Items(min: 1);

        $this->assertSame(1, $items->min);
        $this->assertNull($items->max);
        $this->assertNull($items->unique);
    }

    public function testItemsWithOnlyMax(): void
    {
        $items = new Items(max: 50);

        $this->assertNull($items->min);
        $this->assertSame(50, $items->max);
        $this->assertNull($items->unique);
    }

    public function testItemsWithOnlyUnique(): void
    {
        $items = new Items(unique: true);

        $this->assertNull($items->min);
        $this->assertNull($items->max);
        $this->assertTrue($items->unique);
    }

    public function testItemsWithUniqueSetToFalse(): void
    {
        $items = new Items(unique: false);

        $this->assertFalse($items->unique);
    }

    public function testItemsWithNoParameters(): void
    {
        $items = new Items();

        $this->assertNull($items->min);
        $this->assertNull($items->max);
        $this->assertNull($items->unique);
    }

    public function testItemsWithNamedArguments(): void
    {
        $items = new Items(unique: true, max: 5, min: 1);

        $this->assertSame(1, $items->min);
        $this->assertSame(5, $items->max);
        $this->assertTrue($items->unique);
    }
}
