<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation\Constraint;

final class ArrayConstraint extends AbstractConstraint
{
    public function toJsonSchema(): array
    {
        return match ($this->type) {
            'non-empty-array', 'non-empty-list' => ['minItems' => 1],
            'array-shape' => $this->parseArrayShape(),
            default => [],
        };
    }

    private function parseArrayShape(): array
    {
        if (!\is_array($this->value)) {
            return [];
        }

        $properties = [];
        $required = [];

        foreach ($this->value as $key => $spec) {
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
