<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Exception\GeneratorException;
use Spiral\JsonSchemaGenerator\Schema\Definition;
use Spiral\JsonSchemaGenerator\Schema\Property;
use Spiral\JsonSchemaGenerator\Schema\Type;

class Generator implements GeneratorInterface
{
    protected array $cache = [];

    /**
     * @param class-string|\ReflectionClass $class
     */
    public function generate(string|\ReflectionClass $class): Schema
    {
        if (!$class instanceof \ReflectionClass) {
            try {
                $class = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new GeneratorException($e->getMessage(), $e->getCode(), $e);
            }
        }

        // check cached
        if (isset($this->cache[$class->getName()])) {
            return $this->cache[$class->getName()];
        }

        $schema = new Schema();

        $dependencies = [];

        $constructor = $class->getMethod('__construct');
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        // Generating properties
        foreach ($class->getProperties() as $property) {
            $psc = $this->generateProperty(
                $property,
                $parameters[$property->getName()] ?? null
            );
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
                try {
                    $rdf = new \ReflectionClass($dependency);
                } catch (\ReflectionException $e) {
                    throw new GeneratorException($e->getMessage(), $e->getCode(), $e);
                }

                $definition = $this->generateDefinition($rdf, $rollingDependencies);
                if ($definition === null) {
                    continue;
                }

                $schema->addDefinition($rdf->getShortName(), $definition);
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

    protected function generateDefinition(
        \ReflectionClass $rf,
        array &$dependencies = []
    ): ?Definition {
        $options = [];
        $properties = [];

        if ($rf->isEnum()) {
            // Getting all constrains
            foreach ($rf->getReflectionConstants() as $constant) {
                $value = $constant->getValue();
                \assert($value instanceof \BackedEnum);

                $options[] = $value->value;
            }

            return new Definition(type: $rf->getName(), options: $options, title: $rf->getShortName());
        }

        $constructor = $rf->getMethod('__construct');
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        // class properties
        foreach ($rf->getProperties() as $property) {
            $psc = $this->generateProperty(
                $property,
                $parameters[$property->getName()] ?? null
            );
            if ($psc === null) {
                continue;
            }

            $dependencies = [...$dependencies, ...$psc->getDependencies()];
            $properties[$property->getName()] = $psc;
        }

        return new Definition(type: $rf->getName(), title: $rf->getShortName(), properties: $properties, );
    }

    protected function generateProperty(
        \ReflectionProperty $property,
        ?\ReflectionParameter $parameter = null
    ): ?Property {
        // skipping private and protected properties
        if (!$this->validProperty($property)) {
            return null;
        }

        // Looking for Field attribute
        $title = '';
        $description = '';
        $default = null;

        $attribute = $property->getAttributes(Field::class);
        if ($attribute !== []) {
            /** @var Field $attribute */
            $attribute = $attribute[0]->newInstance();
            $title = $attribute->title;
            $description = $attribute->description;
            $default = $attribute->default;
        }

        if ($default === null && $property->hasDefaultValue()) {
            $default = $property->getDefaultValue();
        }

        if ($parameter !== null && $property->isPromoted() && $parameter->isDefaultValueAvailable()) {
            $default = $parameter->getDefaultValue();
        }

        /**
         * @var \ReflectionNamedType|null $type
         */
        $type = $property->getType();
        if (!$type instanceof \ReflectionNamedType) {
            return null;
        }

        if ($type->isBuiltin()) {
            $options = [];
            if ($type->getName() === Type::Array->value) {
                $class = $this->findListType($property);
                if ($class !== null) {
                    $options[] = $class;
                }
            }

            return new Property(
                Type::fromBuiltIn($type->getName()),
                $options,
                $title,
                $description,
                $default === null && !$type->allowsNull(),
                $default,
            );
        }

        // Class or enum
        $class = $type->getName();

        return \class_exists($class)
            ? new Property($class, [], $title, $description, !$type->allowsNull(), $default, )
            : null;
    }

    // validates property
    private function validProperty(\ReflectionProperty $property): bool
    {
        // skipping private, protected, static properties
        if ($property->isPrivate() || $property->isProtected() || $property->isStatic()) {
            return false;
        }

        // skipping properties with no type, sorry old PHP
        if (!$property->hasType()) {
            return false;
        }

        return true;
    }

    /**
     * @return class-string|Type|null
     */
    private function findListType(\ReflectionProperty $property): null|string|Type
    {
        // example: /** @var list<Movie> */
        // fetching class name using regex, multiline
        $matches = [];
        \preg_match('/@var\s+list<([a-zA-Z0-9_]+)>/', $property->getDocComment(), $matches);
        if (\count($matches) > 0) {
            $className = $matches[1];

            return $this->detectType($property, $className);
        }

        // matching @var ClassName[]
        $matches = [];
        \preg_match('/@var\s+([a-zA-Z0-9_]+)\[\]/', $property->getDocComment(), $matches);
        if (\count($matches) > 0) {
            $className = $matches[1];

            return $this->detectType($property, $className);
        }

        return null;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return class-string|Type
     */
    private function detectType(
        \ReflectionProperty $property,
        string $name
    ): string|Type {
        if (!\str_starts_with($name, '\\')) {
            $parentNamespace = $property->getDeclaringClass()->getNamespaceName();
            $className = $parentNamespace . '\\' . $name;

            if (\class_exists($className)) {
                return $className;
            }
        }

        return Type::fromBuiltIn($name);
    }
}
