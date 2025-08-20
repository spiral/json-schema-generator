<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation\Constraint;

final class StringConstraint extends AbstractConstraint
{
    public function toJsonSchema(): array
    {
        return match ($this->type) {
            'non-empty-string' => ['minLength' => 1],
            'numeric-string' => ['pattern' => '^[0-9]*\.?[0-9]+$'],
            'class-string' => [
                'pattern' => '^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff\\\\]*$',
                'description' => 'Must be a valid PHP class name',
            ],
            default => [],
        };
    }
}
