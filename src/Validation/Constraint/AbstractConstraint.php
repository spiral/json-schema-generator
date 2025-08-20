<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation\Constraint;

abstract class AbstractConstraint
{
    public function __construct(
        protected readonly string $type,
        protected readonly mixed $value = null,
    ) {}

    abstract public function toJsonSchema(): array;

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    protected function isApplicable(string $jsonSchemaType): bool
    {
        return match ($this->type) {
            'positive-int', 'negative-int', 'non-positive-int', 'non-negative-int' => $jsonSchemaType === 'integer',
            'int-range' => $jsonSchemaType === 'integer',
            'non-empty-string', 'numeric-string', 'class-string' => $jsonSchemaType === 'string',
            'non-empty-array', 'array-shape' => $jsonSchemaType === 'array',
            default => false,
        };
    }
}
