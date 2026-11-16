<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use Spiral\JsonSchemaGenerator\Parser\ClassParser;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ReleaseStatus;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class ClassParserTest
{
    public static function collectionsDataProvider(): \Traversable
    {
        yield [new class {
            public array $collection;
        }, []];
        yield [
            new class {
                /**
                 * @var array<array-key, Movie>
                 */
                public array $collection;
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class {
                /**
                 * @var array<Movie>
                 */
                public array $collection;
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class {
                /**
                 * @var Movie[]
                 */
                public array $collection;
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class {
                /**
                 * @var list<Movie>
                 */
                public array $collection;
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var array<array-key, Movie>
                     */
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var array<Movie>
                     */
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var Movie[]
                     */
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                public function __construct(
                    /**
                     * @var list<Movie>
                     */
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                /**
                 * @param array<array-key, Movie> $collection
                 */
                public function __construct(
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                /**
                 * @param array<Movie> $collection
                 */
                public function __construct(
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                /**
                 * @param Movie[] $collection
                 */
                public function __construct(
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
        yield [
            new class([]) {
                /**
                 * @param list<Movie> $collection
                 */
                public function __construct(
                    public array $collection,
                ) {}
            },
            [new SimpleType(Movie::class, false)],
        ];
    }

    public function testGetName(): void
    {
        $parser = new ClassParser(Movie::class);

        Assert::same($parser->getName(), Movie::class);
    }

    public function testGetShortName(): void
    {
        $parser = new ClassParser(Movie::class);

        Assert::same($parser->getShortName(), 'Movie');
    }

    public function testGetProperties(): void
    {
        $parser = new ClassParser(Movie::class);

        $properties = $parser->getProperties();

        Assert::same($properties[0]->getName(), 'title');
        Assert::count($properties[0]->getType()->types, 1);
        Assert::same($properties[0]->getType()->types[0]->getName(), Type::String);
        Assert::false($properties[0]->getType()->allowsNull());
        Assert::false($properties[0]->getType()->types[0]->isCollection());
        Assert::false($properties[0]->hasDefaultValue());

        Assert::same($properties[1]->getName(), 'year');
        Assert::same($properties[1]->getType()->types[0]->getName(), Type::Integer);
        Assert::false($properties[1]->getType()->allowsNull());
        Assert::false($properties[1]->getType()->types[0]->isCollection());
        Assert::false($properties[1]->hasDefaultValue());

        Assert::same($properties[2]->getName(), 'description');
        Assert::same($properties[2]->getType()->types[1]->getName(), Type::String);
        Assert::true($properties[2]->getType()->allowsNull());
        Assert::false($properties[2]->getType()->types[0]->isCollection());
        Assert::true($properties[2]->hasDefaultValue());
        Assert::null($properties[2]->getDefaultValue());

        Assert::same($properties[3]->getName(), 'director');
        Assert::same($properties[3]->getType()->types[1]->getName(), Type::String);
        Assert::true($properties[3]->getType()->allowsNull());
        Assert::false($properties[3]->getType()->types[0]->isCollection());
        Assert::true($properties[3]->hasDefaultValue());
        Assert::null($properties[3]->getDefaultValue());

        Assert::same($properties[4]->getName(), 'releaseStatus');
        Assert::same($properties[4]->getType()->types[0]->getName(), Type::String);
        Assert::same($properties[4]->getType()->types[1]->getName(), Type::Null);
        Assert::true($properties[4]->getType()->types[0]->isEnum());
        Assert::equals($properties[4]->getType()->types[0]->getEnumValues(), ['Released', 'Rumored', 'Post Production', 'In Production', 'Planned', 'Canceled']);
        Assert::true($properties[4]->getType()->allowsNull());
        Assert::false($properties[4]->getType()->types[0]->isCollection());
        Assert::true($properties[4]->hasDefaultValue());
        Assert::null($properties[4]->getDefaultValue());
    }

    public function testIsEnum(): void
    {
        $parser = new ClassParser(Movie::class);
        Assert::false($parser->isEnum());

        $parser = new ClassParser(ReleaseStatus::class);
        Assert::true($parser->isEnum());
    }

    #[DataProvider('collectionsDataProvider')]
    public function testGetPropertyCollectionTypes(object $class, ?array $expected): void
    {
        $parser = new ClassParser($class::class);
        Assert::equals($parser->getProperties()[0]->getType()->types[0]->getCollectionType()?->types, $expected);
    }
}
