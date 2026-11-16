<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class Actor
{
    public function __construct(
        public readonly string $name,

        /**
         * @var array<Movie>
         */
        public readonly ?array $movies = null,
        #[Field(title: 'Best Movie', description: 'The best movie of the actor')]
        public readonly ?Movie $bestMovie = null,
    ) {}
}
