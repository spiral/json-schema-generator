<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Exception\GeneratorException;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * @internal
 */
final class ClassParser implements ClassParserInterface
{
    private readonly \ReflectionClass $class;

    /**
     * @var array<string, \ReflectionParameter>
     */
    private array $constructorParameters = [];
    private readonly PropertyInfoExtractorInterface $propertyInfo;

    /**
     * @param \ReflectionClass|class-string $class
     */
    public function __construct(\ReflectionClass|string $class)
    {
        if (\is_string($class)) {
            try {
                $class = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new GeneratorException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $this->class = $class;
        $this->propertyInfo = $this->createPropertyInfo();

        if ($this->class->hasMethod('__construct')) {
            $constructor = $this->class->getMethod('__construct');
            foreach ($constructor->getParameters() as $parameter) {
                $this->constructorParameters[$parameter->getName()] = $parameter;
            }
        }
    }

    /**
     * @return class-string
     */
    public function getName(): string
    {
        return $this->class->getName();
    }

    /**
     * @return non-empty-string
     */
    public function getShortName(): string
    {
        return $this->class->getShortName();
    }

    /**
     * @return array<PropertyInterface>
     */
    public function getProperties(): array
    {
        $properties = [];
        foreach ($this->class->getProperties() as $property) {
            // skipping private, protected, static properties, properties without type
            if ($property->isPrivate() || $property->isProtected() || $property->isStatic() || !$property->hasType()) {
                continue;
            }

            /**
             * @var \ReflectionNamedType|null $type
             */
            $type = $property->getType();
            if (!$type instanceof \ReflectionNamedType) {
                continue;
            }

            $properties[] = new Property(
                property: $property,
                type: new Type(name: $type->getName(), builtin: $type->isBuiltin(), nullable: $type->allowsNull()),
                hasDefaultValue: $this->hasPropertyDefaultValue($property),
                defaultValue: $this->getPropertyDefaultValue($property),
                collectionValueTypes: $this->getPropertyCollectionTypes($property->getName())
            );
        }

        return $properties;
    }

    public function isEnum(): bool
    {
        return $this->class->isEnum();
    }

    public function getEnumValues(): array
    {
        if (!$this->isEnum()) {
            throw new GeneratorException(\sprintf('Class `%s` is not an enum.', $this->class->getName()));
        }

        $values = [];
        foreach ($this->class->getReflectionConstants() as $constant) {
            $value = $constant->getValue();
            \assert($value instanceof \BackedEnum);

            $values[] = $value->value;
        }

        return $values;
    }

    /**
     * @param non-empty-string $property
     *
     * @return array<TypeInterface>
     */
    private function getPropertyCollectionTypes(string $property): array
    {
        $types = $this->propertyInfo->getTypes($this->class->getName(), $property);

        $collectionTypes = [];
        foreach ($types ?? [] as $type) {
            if ($type->isCollection()) {
                $collectionTypes = [...$type->getCollectionValueTypes(), ...$collectionTypes];
            }
        }

        $result = [];
        foreach ($collectionTypes as $type) {
            /**
             * @var non-empty-string $name
             */
            $name = $type->getBuiltinType() === SchemaType::Object->value
                ? $type->getClassName()
                : $type->getBuiltinType();

            $result[] = new Type(
                name: $name,
                builtin: $type->getBuiltinType() !== SchemaType::Object->value,
                nullable: $type->isNullable()
            );
        }

        return $result;
    }

    private function hasPropertyDefaultValue(\ReflectionProperty $property): bool
    {
        $parameter = $this->constructorParameters[$property->getName()] ?? null;

        return $property->hasDefaultValue() || ($parameter !== null && $parameter->isDefaultValueAvailable());
    }

    private function getPropertyDefaultValue(\ReflectionProperty $property): mixed
    {
        if ($property->hasDefaultValue()) {
            $default = $property->getDefaultValue();
        }

        $parameter = $this->constructorParameters[$property->getName()] ?? null;
        if ($parameter !== null && $property->isPromoted() && $parameter->isDefaultValueAvailable()) {
            $default = $parameter->getDefaultValue();
        }

        return $default ?? null;
    }

    private function createPropertyInfo(): PropertyInfoExtractorInterface
    {
        return new PropertyInfoExtractor(typeExtractors: [
            new PhpStanExtractor(),
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        ]);
    }
}
