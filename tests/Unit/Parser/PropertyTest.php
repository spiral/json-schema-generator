<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Parser\Property;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Testo\Assert;
use Testo\Test;

#[Test]
final class PropertyTest
{
    public function testGetName(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'title'),
            type: new Type(types: []),
            hasDefaultValue: false,
        );

        Assert::same($property->getName(), 'title');
    }

    public function testFindAttribute(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'title'),
            type: new Type(types: []),
            hasDefaultValue: false,
        );

        Assert::equals($property->findAttribute(name: Field::class), new Field(title: 'Title', description: 'The title of the movie'));
    }

    public function testHasDefaultValue(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type([]),
            hasDefaultValue: true,
        );
        Assert::true($property->hasDefaultValue());

        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type([]),
            hasDefaultValue: false,
        );
        Assert::false($property->hasDefaultValue());
    }

    public function testGetDefaultValue(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type([]),
            hasDefaultValue: true,
        );
        Assert::null($property->getDefaultValue());

        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type(types: []),
            hasDefaultValue: true,
            defaultValue: 'foo',
        );
        Assert::same($property->getDefaultValue(), 'foo');
    }

    public function testIsCollection(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'description'),
            type: new Type(types: [new SimpleType(name: 'string', builtin: true)]),
            hasDefaultValue: true,
        );
        Assert::false($property->getType()->types[0]->isCollection());

        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type(types: [new SimpleType(name: Movie::class, builtin: false)]),
            hasDefaultValue: true,
        );
        Assert::false($property->getType()->types[0]->isCollection());

        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'description'),
            type: new Type(types: [new SimpleType(name: 'array', builtin: true, collectionType: new Type(types: [new SimpleType(name: 'string', builtin: true)]))]),
            hasDefaultValue: true,
        );
        Assert::true($property->getType()->types[0]->isCollection());
        Assert::equals($property->getType()->types[0]->getCollectionType()?->types[0]?->getName()->value, 'string');
    }

    public function testGetType(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'description'),
            type: new Type(types: [new SimpleType(name: Movie::class, builtin: false)]),
            hasDefaultValue: true,
        );
        Assert::equals($property->getType()->types[0], new SimpleType(name: Movie::class, builtin: false));
    }
}
