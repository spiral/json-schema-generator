<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Testo\Assert;
use Testo\Test;

#[Test]
final class TypeTest
{
    public function testGetName(): void
    {
        $type = new SimpleType(name: 'string', builtin: true);
        Assert::same($type->getName(), \Spiral\JsonSchemaGenerator\Schema\Type::String);

        $type = new SimpleType(name: Movie::class, builtin: false);
        Assert::same($type->getName(), Movie::class);
    }

    public function testIsBuiltin(): void
    {
        $type = new SimpleType(name: 'string', builtin: true);
        Assert::true($type->isBuiltin());

        $type = new SimpleType(name: Movie::class, builtin: false);
        Assert::false($type->isBuiltin());
    }

    public function testAllowsNull(): void
    {
        $type = new Type(types: [new SimpleType(name: 'null', builtin: true), new SimpleType(name: 'string', builtin: true)]);
        Assert::true($type->allowsNull());

        $type = new Type(types: [new SimpleType(name: Movie::class, builtin: false)]);
        Assert::false($type->allowsNull());
    }
}
