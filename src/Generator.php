<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Parser\ClassParserInterface;
use Spiral\JsonSchemaGenerator\Parser\Parser;
use Spiral\JsonSchemaGenerator\Parser\ParserInterface;
use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Parser\TypeInterface;
use Spiral\JsonSchemaGenerator\Schema\Definition;
use Spiral\JsonSchemaGenerator\Schema\Property;

class Generator implements GeneratorInterface
{
    protected array $cache = [];

    public function __construct(
        protected readonly ParserInterface $parser = new Parser(),
    ) {
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
        if ($class->isEnum()) {
            return new Definition(
                type: $class->getName(),
                options: $class->getEnumValues(),
                title: $class->getShortName()
            );
        }

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

        $attribute = $property->findAttribute(Field::class);
        if ($attribute !== null) {
            $title = $attribute->title;
            $description = $attribute->description;
            $default = $attribute->default;
        }

        if ($default === null && $property->hasDefaultValue()) {
            $default = $property->getDefaultValue();
        }

        $type = $property->getType();

        $options = [];
        if ($property->isCollection()) {
            $options = \array_map(
                static fn (TypeInterface $type) => $type->getName(),
                $property->getCollectionValueTypes()
            );
        }

        $required = $default === null && !$type->allowsNull();
        if ($type->isBuiltin()) {
            return new Property($type->getName(), $options, $title, $description, $required, $default);
        }

        // Class or enum
        $class = $type->getName();

        return \is_string($class) && \class_exists($class)
            ? new Property($class, [], $title, $description, $required, $default)
            : null;
    }
}
