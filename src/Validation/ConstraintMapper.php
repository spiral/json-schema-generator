<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation;

use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Validation\Constraint\AbstractConstraint;
use Spiral\JsonSchemaGenerator\Validation\Constraint\ArrayConstraint;
use Spiral\JsonSchemaGenerator\Validation\Constraint\NumericConstraint;
use Spiral\JsonSchemaGenerator\Validation\Constraint\StringConstraint;

/**
 * @internal
 */
final class ConstraintMapper
{
    public function mapConstraintsToJsonSchema(array $constraints, Type $jsonSchemaType): array
    {
        $validationRules = [];

        foreach ($constraints as $constraint) {
            if (\is_string($constraint)) {
                $constraintObj = $this->createConstraintObject($constraint);
            } elseif (\is_array($constraint)) {
                $constraintObj = $this->createConstraintObjectFromArray($constraint);
            } else {
                continue;
            }

            if ($constraintObj && $this->isConstraintApplicable($constraintObj, $jsonSchemaType)) {
                $rules = $constraintObj->toJsonSchema();
                $validationRules = \array_merge($validationRules, $rules);
            }
        }

        return $validationRules;
    }

    private function createConstraintObject(string $constraint): ?AbstractConstraint
    {
        return match (true) {
            \in_array($constraint, ['positive-int', 'negative-int', 'non-positive-int', 'non-negative-int'], true) =>
            new NumericConstraint($constraint),
            \in_array($constraint, ['non-empty-string', 'numeric-string', 'class-string'], true) =>
            new StringConstraint($constraint),
            \in_array($constraint, ['non-empty-array', 'non-empty-list'], true) =>
            new ArrayConstraint($constraint),
            default => null,
        };
    }

    private function createConstraintObjectFromArray(array $constraint): ?AbstractConstraint
    {
        $type = \array_key_first($constraint);
        $value = $type !== null ? $constraint[$type] : null;

        return match ($type) {
            'int-range' => new NumericConstraint($type, $value),
            'array-shape' => new ArrayConstraint($type, $value),
            default => null,
        };
    }

    private function isConstraintApplicable(AbstractConstraint $constraint, Type $jsonSchemaType): bool
    {
        return match ($constraint->getType()) {
            'positive-int', 'negative-int', 'non-positive-int', 'non-negative-int', 'int-range' =>
                $jsonSchemaType === Type::Integer,
            'non-empty-string', 'numeric-string', 'class-string' =>
                $jsonSchemaType === Type::String,
            'non-empty-array', 'non-empty-list', 'array-shape' =>
                $jsonSchemaType === Type::Array,
            default => false,
        };
    }
}
