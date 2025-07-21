<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Parser\Property;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class PropertyTest extends TestCase
{
    public function testGetName(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'title'),
            type: new Type(types: []),
            hasDefaultValue: false,
        );

        $this->assertSame('title', $property->getName());
    }

    public function testFindAttribute(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'title'),
            type: new Type(types: []),
            hasDefaultValue: false,
        );

        $this->assertEquals(
            new Field(title: 'Title', description: 'The title of the movie'),
            $property->findAttribute(name: Field::class),
        );
    }

    public function testHasDefaultValue(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type([]),
            hasDefaultValue: true,
        );
        $this->assertTrue($property->hasDefaultValue());

        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type([]),
            hasDefaultValue: false,
        );
        $this->assertFalse($property->hasDefaultValue());
    }

    public function testGetDefaultValue(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type([]),
            hasDefaultValue: true,
        );
        $this->assertNull($property->getDefaultValue());

        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type(types: []),
            hasDefaultValue: true,
            defaultValue: 'foo',
        );
        $this->assertSame('foo', $property->getDefaultValue());
    }

    public function testIsCollection(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'description'),
            type: new Type(types: [new SimpleType(name: 'string', builtin: true)]),
            hasDefaultValue: true,
        );
        $this->assertFalse($property->getType()->types[0]->isCollection());

        $property = new Property(
            property: new \ReflectionProperty(Movie::class, 'description'),
            type: new Type(types: [new SimpleType(name: Movie::class, builtin: false)]),
            hasDefaultValue: true,
        );
        $this->assertFalse($property->getType()->types[0]->isCollection());

        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'description'),
            type: new Type(types: [new SimpleType(name: 'array', builtin: true, collectionType: new Type(types: [new SimpleType(name: 'string', builtin: true)]))]),
            hasDefaultValue: true,
        );
        $this->assertTrue($property->getType()->types[0]->isCollection());
        $this->assertEquals('string', $property->getType()->types[0]->getCollectionType()?->types[0]?->getName()->value);
    }

    public function testGetType(): void
    {
        $property = new Property(
            property: new \ReflectionProperty(class: Movie::class, property: 'description'),
            type: new Type(types: [new SimpleType(name: Movie::class, builtin: false)]),
            hasDefaultValue: true,
        );
        $this->assertEquals(new SimpleType(name: Movie::class, builtin: false), $property->getType()->types[0]);
    }
}
