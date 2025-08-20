<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class Actor
{
    public function __construct(
        #[Field(title: 'Name', description: 'The name of the actor')]
        public string $name,
        #[Field(title: 'Age', description: 'The age of the actor')]
        public int $age,
        #[Field(title: 'Biography', description: 'The biography of the actor')]
        public ?string $bio = null,

        /**
         * @var list<Movie|Series>|null
         */
        #[Field(title: 'Filmography', description: 'List of movies and series featuring the actor')]
        public ?array $filmography = null,
        #[Field(title: 'Best Movie', description: 'The best movie of the actor')]
        public ?Movie $bestMovie = null,
        #[Field(title: 'Best Series', description: 'The most prominent series of the actor')]
        public ?Series $bestSeries = null,
    ) {}
}
