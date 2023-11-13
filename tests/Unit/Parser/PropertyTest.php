<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Parser\Property;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Parser\TypeInterface;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class PropertyTest extends TestCase
{
    public function testGetName(): void
    {
        $property = new Property(
            new \ReflectionProperty(Movie::class, 'title'),
            $this->createMock(TypeInterface::class),
            false
        );

        $this->assertSame('title', $property->getName());
    }

    public function testFindAttribute(): void
    {
        $property = new Property(
            new \ReflectionProperty(Movie::class, 'title'),
            $this->createMock(TypeInterface::class),
            false
        );

        $this->assertEquals(
            new Field(title: 'Title', description: 'The title of the movie'),
            $property->findAttribute(Field::class)
        );
    }

    public function testHasDefaultValue(): void
    {
        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            $this->createMock(TypeInterface::class),
            true
        );
        $this->assertTrue($property->hasDefaultValue());

        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            $this->createMock(TypeInterface::class),
            false
        );
        $this->assertFalse($property->hasDefaultValue());
    }

    public function testGetDefaultValue(): void
    {
        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            $this->createMock(TypeInterface::class),
            true
        );
        $this->assertNull($property->getDefaultValue());

        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            $this->createMock(TypeInterface::class),
            true,
            'foo'
        );
        $this->assertSame('foo', $property->getDefaultValue());
    }

    public function testIsCollection(): void
    {
        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            new Type('string', true, false),
            true
        );
        $this->assertFalse($property->isCollection());

        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            new Type(Movie::class, false, false),
            true
        );
        $this->assertFalse($property->isCollection());

        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            new Type('array', true, false),
            true
        );
        $this->assertTrue($property->isCollection());
    }

    public function testGetCollectionValueTypes(): void
    {
        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            $this->createMock(TypeInterface::class),
            true
        );
        $this->assertSame([], $property->getCollectionValueTypes());

        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            $this->createMock(TypeInterface::class),
            true,
            null,
            [new Type(Movie::class, false, false)]
        );
        $this->assertEquals([new Type(Movie::class, false, false)], $property->getCollectionValueTypes());
    }

    public function testGetType(): void
    {
        $property = new Property(
            new \ReflectionProperty(Movie::class, 'description'),
            new Type(Movie::class, false, false),
            true
        );
        $this->assertEquals(new Type(Movie::class, false, false), $property->getType());
    }
}
