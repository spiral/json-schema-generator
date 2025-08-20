<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation;

/**
 * @internal
 */
final class DocBlockParser
{
    private const string VAR_PATTERN = '/@var\s+([^*\n\r]+)/';
    private const string INT_RANGE_PATTERN = '/int<([^>]+)>/';
    private const string ARRAY_SHAPE_PATTERN = '/array\{([^}]+)\}/';

    public function parseDocComment(string $docComment): array
    {
        $constraints = [];

        // Extract @var annotation - now captures everything until */ or newline
        if (\preg_match(self::VAR_PATTERN, $docComment, $matches)) {
            $typeAnnotation = \trim($matches[1]);
            $typeAnnotation = \rtrim($typeAnnotation, ' */'); // Clean up trailing */
            $constraints = \array_merge($constraints, $this->parseTypeAnnotation($typeAnnotation));
        }

        return $constraints;
    }

    private function parseTypeAnnotation(string $type): array
    {
        $constraints = [];

        // Handle simple constraints
        $simpleConstraints = [
            'positive-int',
            'negative-int',
            'non-positive-int',
            'non-negative-int',
            'non-empty-string',
            'numeric-string',
            'class-string',
            'non-empty-array',
            'non-empty-list',
        ];

        foreach ($simpleConstraints as $constraint) {
            if (\str_contains($type, $constraint)) {
                $constraints[] = $constraint;
            }
        }

        // Handle int ranges like int<-42, 1337>
        if (\preg_match(self::INT_RANGE_PATTERN, $type, $matches)) {
            $range = $this->parseIntRange($matches[1]);
            if ($range !== null) {
                $constraints[] = ['int-range' => $range];
            }
        }

        // Handle array shapes like array{foo: string, bar: int}
        if (\preg_match(self::ARRAY_SHAPE_PATTERN, $type, $matches)) {
            $shape = $this->parseArrayShape($matches[1]);
            if ($shape !== []) {
                $constraints[] = ['array-shape' => $shape];
            }
        }

        return $constraints;
    }

    private function parseIntRange(string $rangeStr): ?array
    {
        $parts = \array_map('trim', \explode(',', $rangeStr));

        if (\count($parts) !== 2) {
            return null;
        }

        return [$parts[0], $parts[1]];
    }

    private function parseArrayShape(string $shapeStr): array
    {
        $shape = [];
        $elements = \array_map(\trim(...), \explode(',', $shapeStr));

        foreach ($elements as $element) {
            if (\str_contains($element, ':')) {
                /** @psalm-suppress PossiblyUndefinedArrayOffset */
                [$key, $valueType] = \array_map(\trim(...), \explode(':', $element, 2));
                $shape[$key] = $valueType;
            }
        }

        return $shape;
    }
}
