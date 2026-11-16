<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\Polymorphic;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\Movie;

final class Actor
{
    public function __construct(
        public readonly string $name,

        /**
         * @var list<Movie|Series>|null
         */
        #[Field(title: 'Filmography', description: 'List of movies and series featuring the actor')]
        public readonly ?array $filmography = null,
        #[Field(title: 'Best Movie', description: 'The best movie of the actor')]
        public readonly ?Movie $bestMovie = null,
        #[Field(title: 'Best Series', description: 'The most prominent series of the actor')]
        public readonly ?Series $bestSeries = null,
    ) {}
}
