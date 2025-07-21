<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Actor;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\FlexibleValue;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class GeneratorTest extends TestCase
{
    public function testGenerateMovie(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Movie::class);

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'title'         => [
                        'title'       => 'Title',
                        'description' => 'The title of the movie',
                        'type'        => 'string',
                    ],
                    'year'          => [
                        'title'       => 'Year',
                        'description' => 'The year of the movie',
                        'type'        => 'integer',
                    ],
                    'description'   => [
                        'title'       => 'Description',
                        'description' => 'The description of the movie',
                        'oneOf'       => [
                            ['type' => 'null'],
                            ['type' => 'string'],
                        ],
                    ],
                    'director'      => [
                        'oneOf' => [
                            ['type' => 'null'],
                            ['type' => 'string'],
                        ],
                    ],
                    'releaseStatus' => [
                        'title'       => 'Release Status',
                        'description' => 'The release status of the movie',
                        'oneOf'       => [
                            [
                                'type' => 'null',
                            ],
                            [
                                'type' => 'string',
                                'enum' => ['Released', 'Rumored', 'Post Production', 'In Production', 'Planned', 'Canceled'],
                            ],
                        ],
                    ],
                    'releaseDate'      => [
                        'oneOf'       => [
                            ['type' => 'null'],
                            ['type' => 'string'],
                        ],
                        'format'      => 'date',
                        'title'       => 'Release date',
                        'description' => 'The release date of the movie',
                    ],
                ],
                'required'    => [
                    'title',
                    'year',
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testGenerateActor(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Actor::class);

        $this->assertEquals(
            [
                'type' => 'object',
                'properties'  => [
                    'name' => [
                        'type'        => 'string',
                        'title'       => 'Name',
                        'description' => 'The name of the actor',
                    ],
                    'age' => [
                        'type'        => 'integer',
                        'title'       => 'Age',
                        'description' => 'The age of the actor',
                    ],
                    'bio' => [
                        'title'       => 'Biography',
                        'description' => 'The biography of the actor',
                        'oneOf'       => [
                            ['type' => 'null'],
                            ['type' => 'string'],
                        ],
                    ],
                    'filmography' => [
                        'title'       => 'Filmography',
                        'description' => 'List of movies and series featuring the actor',
                        'oneOf'       => [
                            ['type' => 'null'],
                            [
                                'type'  => 'array',
                                'items' => [
                                    'anyOf' => [
                                        ['$ref' => '#/definitions/Movie'],
                                        ['$ref' => '#/definitions/Series'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'bestMovie' => [
                        'title'       => 'Best Movie',
                        'description' => 'The best movie of the actor',
                        'oneOf'       => [
                            ['type' => 'null'],
                            ['$ref' => '#/definitions/Movie'],
                        ],
                    ],
                    'bestSeries' => [
                        'title'       => 'Best Series',
                        'description' => 'The most prominent series of the actor',
                        'oneOf'       => [
                            ['type' => 'null'],
                            ['$ref' => '#/definitions/Series'],
                        ],
                    ],
                ],
                'required' => [
                    'name',
                    'age',
                ],
                'definitions' => [
                    'Movie' => [
                        'title'      => 'Movie',
                        'type'       => 'object',
                        'properties' => [
                            'title' => [
                                'title'       => 'Title',
                                'description' => 'The title of the movie',
                                'type'        => 'string',
                            ],
                            'year' => [
                                'title'       => 'Year',
                                'description' => 'The year of the movie',
                                'type'        => 'integer',
                            ],
                            'description' => [
                                'title'       => 'Description',
                                'description' => 'The description of the movie',
                                'oneOf'       => [
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
                                'title'       => 'Release date',
                                'description' => 'The release date of the movie',
                                'oneOf'       => [
                                    ['type' => 'null'],
                                    ['type' => 'string'],
                                ],
                                'format' => 'date',
                            ],
                            'releaseStatus' => [
                                'title'       => 'Release Status',
                                'description' => 'The release status of the movie',
                                'oneOf'       => [
                                    ['type' => 'null'],
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
                                ],
                            ],
                        ],
                        'required' => ['title', 'year'],
                    ],
                    'Series' => [
                        'title'      => 'Series',
                        'type'       => 'object',
                        'properties' => [
                            'title' => [
                                'title'       => 'Title',
                                'description' => 'The title of the series',
                                'type'        => 'string',
                            ],
                            'firstAirYear' => [
                                'title'       => 'First Air Year',
                                'description' => 'The year the series first aired',
                                'type'        => 'integer',
                            ],
                            'description' => [
                                'title'       => 'Description',
                                'description' => 'The description of the series',
                                'oneOf'       => [
                                    ['type' => 'null'],
                                    ['type' => 'string'],
                                ],
                            ],
                            'creator' => [
                                'title'       => 'Creator',
                                'description' => 'The creator or showrunner of the series',
                                'oneOf'       => [
                                    ['type' => 'null'],
                                    ['type' => 'string'],
                                ],
                            ],
                            'status' => [
                                'title'       => 'Series Status',
                                'description' => 'The current status of the series',
                                'oneOf'       => [
                                    ['type' => 'null'],
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
                                ],
                            ],
                            'firstAirDate' => [
                                'title'       => 'First Air Date',
                                'description' => 'The original release date of the series',
                                'format'      => 'date',
                                'oneOf'       => [
                                    ['type' => 'null'],
                                    ['type' => 'string'],
                                ],
                            ],
                            'lastAirDate' => [
                                'title'       => 'Last Air Date',
                                'description' => 'The most recent air date of the series',
                                'format'      => 'date',
                                'oneOf'       => [
                                    ['type' => 'null'],
                                    ['type' => 'string'],
                                ],
                            ],
                            'seasons' => [
                                'title'       => 'Seasons',
                                'description' => 'Number of seasons released',
                                'oneOf'       => [
                                    ['type' => 'null'],
                                    ['type' => 'integer'],
                                ],
                            ],
                        ],
                        'required' => ['title', 'firstAirYear'],
                    ],
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testGenerateFlexibleValue(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(FlexibleValue::class);

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'value' => [
                        'title' => 'Value',
                        'description' => 'Can be either string or integer',
                        'oneOf' => [
                            ['type' => 'string'],
                            ['type' => 'integer'],
                        ],
                    ],
                    'flag' => [
                        'title' => 'Optional Flag',
                        'description' => 'Boolean or null',
                        'oneOf' => [
                            ['type' => 'null'],
                            ['type' => 'boolean'],
                        ],
                    ],
                    'flex' => [
                        'title' => 'Flexible Field',
                        'description' => 'Can be string, int, or null',
                        'oneOf' => [
                            ['type' => 'null'],
                            ['type' => 'string'],
                            ['type' => 'integer'],
                        ],
                    ],
                ],
                'required' => ['value'],
            ],
            $schema->jsonSerialize(),
        );
    }
}
