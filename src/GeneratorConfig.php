<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

final readonly class GeneratorConfig
{
    public function __construct(
        public bool $enableValidationConstraints = true,
    ) {}
}
