<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class TypeTest extends TestCase
{
    public function testGetName(): void
    {
        $type = new Type('string', true, false);
        $this->assertSame(\Spiral\JsonSchemaGenerator\Schema\Type::String, $type->getName());

        $type = new Type(Movie::class, false, false);
        $this->assertSame(Movie::class, $type->getName());
    }

    public function testIsBuiltin(): void
    {
        $type = new Type('string', true, false);
        $this->assertTrue($type->isBuiltin());

        $type = new Type(Movie::class, false, false);
        $this->assertFalse($type->isBuiltin());
    }

    public function testAllowsNull(): void
    {
        $type = new Type('string', true, true);
        $this->assertTrue($type->allowsNull());

        $type = new Type(Movie::class, false, false);
        $this->assertFalse($type->allowsNull());
    }
}
