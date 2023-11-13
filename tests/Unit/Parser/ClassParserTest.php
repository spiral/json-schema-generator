<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Exception\GeneratorException;
use Spiral\JsonSchemaGenerator\Parser\ClassParser;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ReleaseStatus;

final class ClassParserTest extends TestCase
{
    public function testGetName(): void
    {
        $parser = new ClassParser(Movie::class);

        $this->assertSame(Movie::class, $parser->getName());
    }

    public function testGetShortName(): void
    {
        $parser = new ClassParser(Movie::class);

        $this->assertSame('Movie', $parser->getShortName());
    }

    public function testGetProperties(): void
    {
        $parser = new ClassParser(Movie::class);

        $properties = $parser->getProperties();

        $this->assertSame('title', $properties[0]->getName());
        $this->assertSame(Type::String, $properties[0]->getType()->getName());
        $this->assertTrue($properties[0]->getType()->isBuiltin());
        $this->assertFalse($properties[0]->getType()->allowsNull());
        $this->assertFalse($properties[0]->isCollection());
        $this->assertFalse($properties[0]->hasDefaultValue());

        $this->assertSame('year', $properties[1]->getName());
        $this->assertSame(Type::Integer, $properties[1]->getType()->getName());
        $this->assertTrue($properties[1]->getType()->isBuiltin());
        $this->assertFalse($properties[1]->getType()->allowsNull());
        $this->assertFalse($properties[1]->isCollection());
        $this->assertFalse($properties[1]->hasDefaultValue());

        $this->assertSame('description', $properties[2]->getName());
        $this->assertSame(Type::String, $properties[2]->getType()->getName());
        $this->assertTrue($properties[2]->getType()->isBuiltin());
        $this->assertTrue($properties[2]->getType()->allowsNull());
        $this->assertFalse($properties[2]->isCollection());
        $this->assertTrue($properties[2]->hasDefaultValue());
        $this->assertNull($properties[2]->getDefaultValue());

        $this->assertSame('director', $properties[3]->getName());
        $this->assertSame(Type::String, $properties[3]->getType()->getName());
        $this->assertTrue($properties[3]->getType()->isBuiltin());
        $this->assertTrue($properties[3]->getType()->allowsNull());
        $this->assertFalse($properties[3]->isCollection());
        $this->assertTrue($properties[3]->hasDefaultValue());
        $this->assertNull($properties[3]->getDefaultValue());

        $this->assertSame('releaseStatus', $properties[4]->getName());
        $this->assertSame(ReleaseStatus::class, $properties[4]->getType()->getName());
        $this->assertFalse($properties[4]->getType()->isBuiltin());
        $this->assertTrue($properties[4]->getType()->allowsNull());
        $this->assertFalse($properties[4]->isCollection());
        $this->assertTrue($properties[4]->hasDefaultValue());
        $this->assertNull($properties[4]->getDefaultValue());
    }

    public function testIsEnum(): void
    {
        $parser = new ClassParser(Movie::class);
        $this->assertFalse($parser->isEnum());

        $parser = new ClassParser(ReleaseStatus::class);
        $this->assertTrue($parser->isEnum());
    }

    public function testGetEnumValues(): void
    {
        $parser = new ClassParser(ReleaseStatus::class);
        $this->assertSame(
            [
                'Released',
                'Rumored',
                'Post Production',
                'In Production',
                'Planned',
                'Canceled',
            ],
            $parser->getEnumValues()
        );
    }

    public function testGetEnumValuesException(): void
    {
        $parser = new ClassParser(Movie::class);
        $this->expectException(GeneratorException::class);
        $parser->getEnumValues();
    }

    #[DataProvider('collectionsDataProvider')]
    public function testGetPropertyCollectionTypes(object $class, array $expected): void
    {
        $parser = new ClassParser($class::class);
        $this->assertEquals($expected, $parser->getProperties()[0]->getCollectionValueTypes());
    }

    public static function collectionsDataProvider(): \Traversable
    {
        yield [new class {public array $collection;}, []];
        yield [
            new class {
                /**
                 * @var array<array-key, \Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie>
                 */
                public array $collection;
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class {
                /**
                 * @var array<\Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie>
                 */
                public array $collection;
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class {
                /**
                 * @var \Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie[]
                 */
                public array $collection;
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class {
                /**
                 * @var list<\Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie>
                 */
                public array $collection;
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var array<array-key, \Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie>
                     */
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var array<\Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie>
                     */
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var \Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie[]
                     */
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var list<\Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie>
                     */
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                /**
                 * @param array<array-key, \Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie> $collection
                 */
                public function __construct(
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                /**
                 * @param array<\Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie> $collection
                 */
                public function __construct(
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                /**
                 * @param \Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie[] $collection
                 */
                public function __construct(
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
        yield [
            new class([]) {
                /**
                 * @param list<\Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie> $collection
                 */
                public function __construct(
                    public array $collection
                ) {
                }
            },
            [new \Spiral\JsonSchemaGenerator\Parser\Type(Movie::class, false, false)]
        ];
    }
}
