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

class Generator implements GeneratorInterface
{
    protected array $cache = [];

    public function __construct(
        protected readonly ParserInterface $parser = new Parser(),
    ) {}

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

        return new Property(
            types: $this->extractPropertyTypes($type),
            title: $title,
            description: $description,
            required: $default === null && !$type->allowsNull(),
            default: $default,
            format: $format,
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
            collectionTypes: $simpleType->isCollection() ? \array_map(static fn(SimpleType $collectionSimpleType) => new PropertyType(
                type: $collectionSimpleType->getName(),
                enum: $collectionSimpleType->getEnumValues(),
            ), $simpleType->getCollectionType()?->types ?? []) : null,
        ), $type->types);
    }
}
