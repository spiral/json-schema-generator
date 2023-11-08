<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class Actor
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        #[Field(title: 'Biography', description: 'The biography of the actor')]
        public readonly ?string $bio = null,
        /**
         * @var list<Movie>
         */
        public readonly array $movies = [],
        #[Field(title: 'Best Movie', description: 'The best movie of the actor')]
        public readonly ?Movie $bestMovie = null,
    ) {
    }
}
