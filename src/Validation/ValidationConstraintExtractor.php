<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation;

use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Schema\Type;

final readonly class ValidationConstraintExtractor implements PropertyDataExtractorInterface
{
    public function __construct(
        private DocBlockParser $docBlockParser = new DocBlockParser(),
        private ConstraintMapper $constraintMapper = new ConstraintMapper(),
    ) {}

    public function extractValidationRules(PropertyInterface $property, Type $jsonSchemaType): array
    {
        $docComment = $this->getPropertyDocComment($property);

        if ($docComment === null) {
            return [];
        }

        $constraints = $this->docBlockParser->parseDocComment($docComment);

        return $this->constraintMapper->mapConstraintsToJsonSchema($constraints, $jsonSchemaType);
    }

    private function getPropertyDocComment(PropertyInterface $property): ?string
    {
        // We need to access the ReflectionProperty to get doc comment
        // This requires extending PropertyInterface or accessing it differently

        // For now, we'll use reflection to get the property's doc comment
        // This is a bit hacky but necessary without changing the existing interface

        $reflection = new \ReflectionClass($property);
        $propertyReflection = null;

        // Try to get the ReflectionProperty from the Property object
        try {
            $propertyField = $reflection->getProperty('property');
            $propertyReflection = $propertyField->getValue($property);
        } catch (\ReflectionException) {
            return null;
        }

        if (!$propertyReflection instanceof \ReflectionProperty) {
            return null;
        }

        $docComment = $propertyReflection->getDocComment();
        return $docComment === false ? null : $docComment;
    }
}
