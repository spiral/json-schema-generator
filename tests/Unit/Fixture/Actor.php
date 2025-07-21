<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class Actor
{
    public function __construct(
        #[Field(title: 'Name', description: 'The name of the actor')]
        public readonly string $name,
        #[Field(title: 'Age', description: 'The age of the actor')]
        public readonly int $age,
        #[Field(title: 'Biography', description: 'The biography of the actor')]
        public readonly ?string $bio = null,

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
