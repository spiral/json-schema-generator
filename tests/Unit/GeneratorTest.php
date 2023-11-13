<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Actor;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class GeneratorTest extends TestCase
{
    public function testGenerateMovie(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Movie::class);

        $this->assertEquals(
            [
                'properties'  => [
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
                        'type'        => 'string',
                    ],
                    'director'      => [
                        'type' => 'string',
                    ],
                    'releaseStatus' => [
                        'title'       => 'Release Status',
                        'description' => 'The release status of the movie',
                        'allOf'       => [
                            [
                                '$ref' => '#/definitions/ReleaseStatus',
                            ],
                        ],
                    ],
                ],
                'required'    => [
                    'title',
                    'year',
                ],
                'definitions' => [
                    'ReleaseStatus' => [
                        'title' => 'ReleaseStatus',
                        'type'  => 'string',
                        'enum'  => [
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
            $schema->jsonSerialize()
        );
    }

    public function testGenerateActor(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Actor::class);

        $this->assertEquals(
            [
                'properties'  => [
                    'name'      => [
                        'type' => 'string',
                    ],
                    'age'       => [
                        'type' => 'integer',
                    ],
                    'bio'       => [
                        'title'       => 'Biography',
                        'description' => 'The biography of the actor',
                        'type'        => 'string',
                    ],
                    'movies'    => [
                        'type'  => 'array',
                        'items' => [
                            '$ref' => '#/definitions/Movie',
                        ],
                        'default' => [],
                    ],
                    'bestMovie' => [
                        'title'       => 'Best Movie',
                        'description' => 'The best movie of the actor',
                        'allOf'       => [
                            [
                                '$ref' => '#/definitions/Movie',
                            ],
                        ],
                    ],
                ],
                'required'    => [
                    'name',
                    'age',
                ],
                'definitions' => [
                    'Movie'         => [
                        'title'      => 'Movie',
                        'type'       => 'object',
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
                                'type'        => 'string',
                            ],
                            'director'      => [
                                'type' => 'string',
                            ],
                            'releaseStatus' => [
                                'title'       => 'Release Status',
                                'description' => 'The release status of the movie',
                                'allOf'       => [
                                    [
                                        '$ref' => '#/definitions/ReleaseStatus',
                                    ],
                                ],
                            ],
                        ],
                        'required'   => [
                            'title',
                            'year',
                        ],
                    ],
                    'ReleaseStatus' => [
                        'title' => 'ReleaseStatus',
                        'type'  => 'string',
                        'enum'  => [
                            'Released',
                            'Rumored',
                            'Post Production',
                            'In Production',
                            'Planned',
                            'Canceled',
                        ],
                    ]
                ],
            ],
            $schema->jsonSerialize()
        );
    }
}
