<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class TypeTest extends TestCase
{
    public function testGetName(): void
    {
        $type = new SimpleType(name: 'string', builtin: true);
        $this->assertSame(\Spiral\JsonSchemaGenerator\Schema\Type::String, $type->getName());

        $type = new SimpleType(name: Movie::class, builtin: false);
        $this->assertSame(Movie::class, $type->getName());
    }

    public function testIsBuiltin(): void
    {
        $type = new SimpleType(name: 'string', builtin: true);
        $this->assertTrue($type->isBuiltin());

        $type = new SimpleType(name: Movie::class, builtin: false);
        $this->assertFalse($type->isBuiltin());
    }

    public function testAllowsNull(): void
    {
        $type = new Type(types: [new SimpleType(name: 'null', builtin: true), new SimpleType(name: 'string', builtin: true)]);
        $this->assertTrue($type->allowsNull());

        $type = new Type(types: [new SimpleType(name: Movie::class, builtin: false)]);
        $this->assertFalse($type->allowsNull());
    }
}
