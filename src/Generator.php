<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Parser\ClassParserInterface;
use Spiral\JsonSchemaGenerator\Parser\Parser;
use Spiral\JsonSchemaGenerator\Parser\ParserInterface;
use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Schema\Definition;
use Spiral\JsonSchemaGenerator\Schema\Property;
use Spiral\JsonSchemaGenerator\Schema\PropertyType;
use Spiral\JsonSchemaGenerator\Validation\ValidationConstraintExtractor;
use Spiral\JsonSchemaGenerator\Validation\AttributeConstraintExtractor;

final class Generator implements GeneratorInterface
{
    protected array $cache = [];
    private readonly ValidationConstraintExtractor $validationExtractor;
    private readonly AttributeConstraintExtractor $attributeExtractor;

    public function __construct(
        protected readonly ParserInterface $parser = new Parser(),
        ?ValidationConstraintExtractor $validationExtractor = null,
        protected readonly GeneratorConfig $config = new GeneratorConfig(),
        ?AttributeConstraintExtractor $attributeExtractor = null,
    ) {
        $this->validationExtractor = $validationExtractor ?? new ValidationConstraintExtractor();
        $this->attributeExtractor = $attributeExtractor ?? new AttributeConstraintExtractor();
    }

    /**
     * @param class-string|\ReflectionClass $class
     */
    public function generate(string|\ReflectionClass $class): Schema
    {
        $class = $this->parser->parse($class);

        // check cached
        if (isset($this->cache[$class->getName()])) {
            return $this->cache[$class->getName()];
        }

        $schema = new Schema();

        $dependencies = [];
        // Generating properties
        foreach ($class->getProperties() as $property) {
            $psc = $this->generateProperty($property);
            if ($psc === null) {
                continue;
            }

            // does it refer to any other classes
            $dependencies = [...$dependencies, ...$psc->getDependencies()];

            $schema->addProperty($property->getName(), $psc);
        }

        // Generating dependencies
        $dependencies = \array_unique($dependencies);
        $rollingDependencies = [];
        $doneDependencies = [];

        do {
            foreach ($dependencies as $dependency) {
                $dependency = $this->parser->parse($dependency);
                $definition = $this->generateDefinition($dependency, $rollingDependencies);
                if ($definition === null) {
                    continue;
                }

                $schema->addDefinition($dependency->getShortName(), $definition);
            }

            $doneDependencies = [...$doneDependencies, ...$dependencies];
            $rollingDependencies = \array_diff($rollingDependencies, $doneDependencies);
            if ($rollingDependencies === []) {
                break;
            }

            $dependencies = $rollingDependencies;
        } while (true);

        // caching
        $this->cache[$class->getName()] = $schema;

        return $schema;
    }

    protected function generateDefinition(ClassParserInterface $class, array &$dependencies = []): ?Definition
    {
        $properties = [];
        // class properties
        foreach ($class->getProperties() as $property) {
            $psc = $this->generateProperty($property);
            if ($psc === null) {
                continue;
            }

            $dependencies = [...$dependencies, ...$psc->getDependencies()];
            $properties[$property->getName()] = $psc;
        }

        return new Definition(type: $class->getName(), title: $class->getShortName(), properties: $properties);
    }

    protected function generateProperty(PropertyInterface $property): ?Property
    {
        // Looking for Field attribute
        $title = '';
        $description = '';
        $default = null;
        $format = null;

        $attribute = $property->findAttribute(Field::class);
        if ($attribute !== null) {
            $title = $attribute->title;
            $description = $attribute->description;
            $default = $attribute->default;
            $format = $attribute->format;
        }

        if ($default === null && $property->hasDefaultValue()) {
            $default = $property->getDefaultValue();
        }

        $type = $property->getType();
        $propertyTypes = $this->extractPropertyTypes($type);

        // Extract validation constraints from PHPDoc (if enabled)
        $validationRules = [];
        if ($this->config->enableValidationConstraints) {
            $validationRules = $this->extractValidationConstraints($property, $propertyTypes);
            $validationRules = \array_merge(
                $validationRules,
                $this->extractAttributeConstraints($property, $propertyTypes),
            );
        }

        return new Property(
            types: $propertyTypes,
            title: $title,
            description: $description,
            required: $default === null && !$type->allowsNull(),
            default: $default,
            format: $format,
            validationRules: $validationRules,
        );
    }

    /**
     * @return list<PropertyType>
     */
    private function extractPropertyTypes(Type $type): array
    {
        return \array_map(static fn(SimpleType $simpleType) => new PropertyType(
            type: $simpleType->getName(),
            enum: $simpleType->getEnumValues(),
            collectionTypes: $simpleType->isCollection() ? \array_map(
                static fn(SimpleType $collectionSimpleType) => new PropertyType(
                    type: $collectionSimpleType->getName(),
                    enum: $collectionSimpleType->getEnumValues(),
                ),
                $simpleType->getCollectionType()?->types ?? [],
            ) : null,
        ), $type->types);
    }

    /**
     * Extract validation constraints from property PHPDoc
     */
    private function extractValidationConstraints(PropertyInterface $property, array $propertyTypes): array
    {
        $allValidationRules = [];

        foreach ($propertyTypes as $propertyType) {
            if ($propertyType->type instanceof Schema\Type) {
                $validationRules = $this->validationExtractor->extractValidationRules($property, $propertyType->type);
                $allValidationRules = \array_merge($allValidationRules, $validationRules);
            }
        }

        return $allValidationRules;
    }

    /**
     * Extract validation constraints from property attributes
     */
    private function extractAttributeConstraints(PropertyInterface $property, array $propertyTypes): array
    {
        $allValidationRules = [];

        foreach ($propertyTypes as $propertyType) {
            if ($propertyType->type instanceof Schema\Type) {
                $validationRules = $this->attributeExtractor->extractValidationRules($property, $propertyType->type);
                $allValidationRules = \array_merge($allValidationRules, $validationRules);
            }
        }

        return $allValidationRules;
    }
}
