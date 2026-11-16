<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation\Constraint;

/**
 * @internal
 */
final class ArrayConstraint extends AbstractConstraint
{
    #[\Override]
    public function toJsonSchema(): array
    {
        return match ($this->type) {
            'non-empty-array', 'non-empty-list' => ['minItems' => 1],
            'array-shape' => $this->parseArrayShape(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function parseArrayShape(): array
    {
        if (!\is_array($this->value)) {
            return [];
        }

        $properties = [];
        $required = [];

        /** @var array<array-key, string> $shape */
        $shape = $this->value;

        // A tuple shape (`array{0: string, 1: int}`) cannot become an object schema:
        // PHP turns the numeric keys back into a list, which serializes as a JSON array.
        foreach ($shape as $key => $spec) {
            if (\is_int($key)) {
                return [];
            }

            $isOptional = \str_ends_with($key, '?');
            $cleanKey = $isOptional ? \rtrim($key, '?') : $key;

            if (!$isOptional) {
                $required[] = $cleanKey;
            }

            // Basic type mapping for shaped array elements
            $properties[$cleanKey] = match ($spec) {
                'string' => ['type' => 'string'],
                'int', 'integer' => ['type' => 'integer'],
                'float', 'number' => ['type' => 'number'],
                'bool', 'boolean' => ['type' => 'boolean'],
                default => ['type' => 'string'], // fallback
            };
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
            'additionalProperties' => false, // Shaped arrays are strict
        ];

        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }
}
