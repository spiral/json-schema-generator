<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Actor;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\FlexibleValue;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Library;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\PropertyVisibility;
use Testo\Assert;
use Testo\Test;

#[Test]
final class GeneratorTest
{
    public function testGenerateMovie(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Movie::class);

        Assert::equals($schema->jsonSerialize(), [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'title' => 'Title',
                    'description' => 'The title of the movie',
                    'type' => 'string',
                ],
                'year' => [
                    'title' => 'Year',
                    'description' => 'The year of the movie',
                    'type' => 'integer',
                ],
                'description' => [
                    'title' => 'Description',
                    'description' => 'The description of the movie',
                    'oneOf' => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                ],
                'director' => [
                    'oneOf' => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                ],
                'releaseStatus' => [
                    'title' => 'Release Status',
                    'description' => 'The release status of the movie',
                    'oneOf' => [
                        [
                            'type' => 'string',
                            'enum' => [
                                'Released',
                                'Rumored',
                                'Post Production',
                                'In Production',
                                'Planned',
                                'Canceled',
                            ],
                        ],
                        [
                            'type' => 'null',
                        ],
                    ],
                ],
                'releaseDate' => [
                    'oneOf' => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                    'format' => 'date',
                    'title' => 'Release date',
                    'description' => 'The release date of the movie',
                ],
            ],
            'required' => [
                'title',
                'year',
            ],
        ]);
    }

    public function testGenerateActor(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Actor::class);

        Assert::equals($schema->jsonSerialize(), [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'title' => 'Name',
                    'description' => 'The name of the actor',
                ],
                'age' => [
                    'type' => 'integer',
                    'title' => 'Age',
                    'description' => 'The age of the actor',
                ],
                'bio' => [
                    'title' => 'Biography',
                    'description' => 'The biography of the actor',
                    'oneOf' => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                ],
                'filmography' => [
                    'title' => 'Filmography',
                    'description' => 'List of movies and series featuring the actor',
                    'oneOf' => [
                        [
                            'type' => 'array',
                            'items' => [
                                'anyOf' => [
                                    ['$ref' => '#/definitions/Movie'],
                                    ['$ref' => '#/definitions/Series'],
                                ],
                            ],
                        ],
                        ['type' => 'null'],
                    ],
                ],
                'bestMovie' => [
                    'title' => 'Best Movie',
                    'description' => 'The best movie of the actor',
                    'oneOf' => [
                        ['$ref' => '#/definitions/Movie'],
                        ['type' => 'null'],
                    ],
                ],
                'bestSeries' => [
                    'title' => 'Best Series',
                    'description' => 'The most prominent series of the actor',
                    'oneOf' => [
                        ['$ref' => '#/definitions/Series'],
                        ['type' => 'null'],
                    ],
                ],
            ],
            'required' => [
                'name',
                'age',
            ],
            'definitions' => [
                'Movie' => [
                    'title' => 'Movie',
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'title' => 'Title',
                            'description' => 'The title of the movie',
                            'type' => 'string',
                        ],
                        'year' => [
                            'title' => 'Year',
                            'description' => 'The year of the movie',
                            'type' => 'integer',
                        ],
                        'description' => [
                            'title' => 'Description',
                            'description' => 'The description of the movie',
                            'oneOf' => [
                                ['type' => 'null'],
                                ['type' => 'string'],
                            ],
                        ],
                        'director' => [
                            'oneOf' => [
                                ['type' => 'null'],
                                ['type' => 'string'],
                            ],
                        ],
                        'releaseDate' => [
                            'title' => 'Release date',
                            'description' => 'The release date of the movie',
                            'oneOf' => [
                                ['type' => 'null'],
                                ['type' => 'string'],
                            ],
                            'format' => 'date',
                        ],
                        'releaseStatus' => [
                            'title' => 'Release Status',
                            'description' => 'The release status of the movie',
                            'oneOf' => [
                                [
                                    'type' => 'string',
                                    'enum' => [
                                        'Released',
                                        'Rumored',
                                        'Post Production',
                                        'In Production',
                                        'Planned',
                                        'Canceled',
                                    ],
                                ],
                                ['type' => 'null'],
                            ],
                        ],
                    ],
                    'required' => ['title', 'year'],
                ],
                'Series' => [
                    'title' => 'Series',
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'title' => 'Title',
                            'description' => 'The title of the series',
                            'type' => 'string',
                        ],
                        'firstAirYear' => [
                            'title' => 'First Air Year',
                            'description' => 'The year the series first aired',
                            'type' => 'integer',
                        ],
                        'description' => [
                            'title' => 'Description',
                            'description' => 'The description of the series',
                            'oneOf' => [
                                ['type' => 'null'],
                                ['type' => 'string'],
                            ],
                        ],
                        'creator' => [
                            'title' => 'Creator',
                            'description' => 'The creator or showrunner of the series',
                            'oneOf' => [
                                ['type' => 'null'],
                                ['type' => 'string'],
                            ],
                        ],
                        'status' => [
                            'title' => 'Series Status',
                            'description' => 'The current status of the series',
                            'oneOf' => [
                                [
                                    'type' => 'string',
                                    'enum' => [
                                        'Airing',
                                        'Ended',
                                        'Canceled',
                                        'Upcoming',
                                        'Hiatus',
                                    ],
                                ],
                                ['type' => 'null'],
                            ],
                        ],
                        'firstAirDate' => [
                            'title' => 'First Air Date',
                            'description' => 'The original release date of the series',
                            'format' => 'date',
                            'oneOf' => [
                                ['type' => 'null'],
                                ['type' => 'string'],
                            ],
                        ],
                        'lastAirDate' => [
                            'title' => 'Last Air Date',
                            'description' => 'The most recent air date of the series',
                            'format' => 'date',
                            'oneOf' => [
                                ['type' => 'null'],
                                ['type' => 'string'],
                            ],
                        ],
                        'seasons' => [
                            'title' => 'Seasons',
                            'description' => 'Number of seasons released',
                            'oneOf' => [
                                ['type' => 'integer'],
                                ['type' => 'null'],
                            ],
                        ],
                    ],
                    'required' => ['title', 'firstAirYear'],
                ],
            ],
        ]);
    }

    public function testGenerateFlexibleValue(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(FlexibleValue::class);

        Assert::equals($schema->jsonSerialize(), [
            'type' => 'object',
            'properties' => [
                'value' => [
                    'title' => 'Value',
                    'description' => 'Can be either string or integer',
                    'oneOf' => [
                        ['type' => 'integer'],
                        ['type' => 'string'],
                    ],
                ],
                'flag' => [
                    'title' => 'Optional Flag',
                    'description' => 'Boolean or null',
                    'oneOf' => [
                        ['type' => 'boolean'],
                        ['type' => 'null'],
                    ],
                ],
                'flex' => [
                    'title' => 'Flexible Field',
                    'description' => 'Can be string, int, or null',
                    'oneOf' => [
                        ['type' => 'integer'],
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                ],
            ],
            'required' => ['value'],
        ]);
    }

    public function testTransitiveDependenciesBecomeDefinitions(): void
    {
        $schema = \json_decode(\json_encode((new Generator())->generate(Library::class)), true);

        Assert::same($schema['properties'], ['shelf' => ['$ref' => '#/definitions/Shelf']]);
        Assert::same(\array_keys($schema['definitions']), ['Shelf', 'Book']);
        Assert::same($schema['definitions']['Shelf']['properties'], [
            'featured' => ['$ref' => '#/definitions/Book'],
            'books' => [
                'default' => [],
                'type' => 'array',
                'items' => ['$ref' => '#/definitions/Book'],
            ],
        ]);
        Assert::same($schema['definitions']['Book']['properties']['priority'], [
            'default' => 1,
            'type' => 'integer',
            'enum' => [1, 2],
        ]);
    }

    public function testOnlyPublicTypedInstancePropertiesAreIncluded(): void
    {
        $schema = (new Generator())->generate(PropertyVisibility::class)->jsonSerialize();

        Assert::same(\array_keys($schema['properties']), ['public']);
    }

    public function testSchemaIsCachedPerClass(): void
    {
        $generator = new Generator();

        Assert::same($generator->generate(Library::class), $generator->generate(Library::class));
    }
}
