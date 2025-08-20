<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation\Constraint;

final class NumericConstraint extends AbstractConstraint
{
    public function toJsonSchema(): array
    {
        return match ($this->type) {
            'positive-int' => ['minimum' => 1],
            'negative-int' => ['maximum' => -1],
            'non-positive-int' => ['maximum' => 0],
            'non-negative-int' => ['minimum' => 0],
            'int-range' => $this->parseIntRange(),
            default => [],
        };
    }

    private function parseIntRange(): array
    {
        if (!\is_array($this->value) || \count($this->value) !== 2) {
            return [];
        }

        [$min, $max] = $this->value;
        $schema = [];

        if ($min !== 'min' && \is_numeric($min)) {
            $schema['minimum'] = (int) $min;
        }

        if ($max !== 'max' && \is_numeric($max)) {
            $schema['maximum'] = (int) $max;
        }

        return $schema;
    }
}
