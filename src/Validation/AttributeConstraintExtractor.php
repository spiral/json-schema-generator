<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Enum;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Items;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\MultipleOf;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Pattern;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;
use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Schema\Type;

final readonly class AttributeConstraintExtractor implements PropertyDataExtractorInterface
{
    public function extractValidationRules(PropertyInterface $property, Type $jsonSchemaType): array
    {
        $validationRules = [];

        // Extract Pattern constraint
        $pattern = $property->findAttribute(Pattern::class);
        if ($pattern instanceof Pattern) {
            $validationRules['pattern'] = $pattern->pattern;
        }

        // Extract Length constraint
        $length = $property->findAttribute(Length::class);
        if ($length instanceof Length) {
            if ($length->min !== null) {
                $validationRules[$this->getLengthMinKey($jsonSchemaType)] = $length->min;
            }
            if ($length->max !== null) {
                $validationRules[$this->getLengthMaxKey($jsonSchemaType)] = $length->max;
            }
        }

        // Extract Range constraint
        $range = $property->findAttribute(Range::class);
        if ($range instanceof Range) {
            if ($range->min !== null) {
                $key = $range->exclusiveMin === true ? 'exclusiveMinimum' : 'minimum';
                $validationRules[$key] = $range->min;
            }
            if ($range->max !== null) {
                $key = $range->exclusiveMax === true ? 'exclusiveMaximum' : 'maximum';
                $validationRules[$key] = $range->max;
            }
        }

        // Extract MultipleOf constraint
        $multipleOf = $property->findAttribute(MultipleOf::class);
        if ($multipleOf instanceof MultipleOf && $this->isNumericType($jsonSchemaType)) {
            $validationRules['multipleOf'] = $multipleOf->value;
        }

        // Extract Items constraint
        $items = $property->findAttribute(Items::class);
        if ($items instanceof Items && $jsonSchemaType === Type::Array) {
            if ($items->min !== null) {
                $validationRules['minItems'] = $items->min;
            }
            if ($items->max !== null) {
                $validationRules['maxItems'] = $items->max;
            }
            if ($items->unique === true) {
                $validationRules['uniqueItems'] = true;
            }
        }

        // Extract Enum constraint
        $enum = $property->findAttribute(Enum::class);
        if ($enum instanceof Enum) {
            $validationRules['enum'] = $enum->values;
        }

        return $validationRules;
    }

    private function getLengthMinKey(Type $jsonSchemaType): string
    {
        return match ($jsonSchemaType) {
            Type::Array => 'minItems',
            default => 'minLength', // fallback
        };
    }

    private function getLengthMaxKey(Type $jsonSchemaType): string
    {
        return match ($jsonSchemaType) {
            Type::Array => 'maxItems',
            default => 'maxLength', // fallback
        };
    }

    private function isNumericType(Type $jsonSchemaType): bool
    {
        return $jsonSchemaType === Type::Integer || $jsonSchemaType === Type::Number;
    }
}
