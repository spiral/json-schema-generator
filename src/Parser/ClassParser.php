<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Exception\GeneratorException;
use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type as PropertyInfoType;

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

        $constructor = $this->class->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->isPromoted()) {
                    $this->constructorParameters[$parameter->getName()] = $parameter;
                }
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
             * @var \ReflectionNamedType|\ReflectionUnionType|null $type
             */
            $type = $property->getType();
            if (!$type instanceof \ReflectionNamedType && !$type instanceof \ReflectionUnionType) {
                continue;
            }

            $properties[] = new Property(
                property: $property,
                type: $this->getPropertyType($property),
                hasDefaultValue: $this->hasPropertyDefaultValue($property),
                defaultValue: $this->getPropertyDefaultValue($property),
            );
        }

        return $properties;
    }

    public function isEnum(): bool
    {
        return $this->class->isEnum();
    }

    /**
     * @param non-empty-string|class-string $typeName
     *
     * @return list<int|string>|null
     */
    private function getEnumValues(string $typeName): ?array
    {
        if (!\is_subclass_of($typeName, \BackedEnum::class)) {
            return null;
        }

        $reflectionEnum = new \ReflectionEnum($typeName);

        return \array_map(
            static fn(\ReflectionEnumUnitCase $case): int|string => $case->getValue()->value,
            $reflectionEnum->getCases(),
        );
    }

    /**
     * @param non-empty-string $typeName
     */
    private function getTypeBuildIn(string $typeName): bool
    {
        if ((\class_exists($typeName))) {
            return \is_subclass_of($typeName, \BackedEnum::class);
        }

        return $typeName !== SchemaType::Object->value;
    }

    /**
     * @param non-empty-string|class-string $typeName
     *
     * @return non-empty-string|class-string
     */
    private function getEnumTypeName(string $typeName): string
    {
        if (!\is_subclass_of($typeName, \BackedEnum::class)) {
            return $typeName;
        }

        $reflection = new \ReflectionEnum($typeName);
        $backingType = $reflection->getBackingType();

        if (!$backingType instanceof \ReflectionNamedType) {
            return $typeName;
        }

        return $backingType->getName();
    }

    private function getPropertyType(\ReflectionProperty $property): Type
    {
        /** @psalm-suppress DeprecatedMethod, DeprecatedClass */
        $types = $this->propertyInfo->getTypes($property->class, $property->getName());

        $simpleTypes = [];
        $isNullable = false;
        foreach ($types ?? [] as $type) {
            $typeName = $type->getBuiltinType() === SchemaType::Object->value && $type->getClassName() !== null
                ? $type->getClassName()
                : $type->getBuiltinType();

            if ($typeName === '') {
                throw new InvalidTypeException();
            }
            if ($type->isNullable() && $isNullable === false) {
                $simpleTypes[] = new SimpleType(
                    name: SchemaType::Null->value,
                    builtin: true,
                );
                $isNullable = true;
            }

            $simpleTypes[] = new SimpleType(
                name: $this->getEnumTypeName($typeName),
                builtin: $this->getTypeBuildIn($typeName),
                collectionType: $this->getCollectionValueType($type),
                enum: $this->getEnumValues($typeName),
            );
        }

        return new Type(types: $simpleTypes);
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getCollectionValueType(PropertyInfoType $propertyInfoType): ?Type
    {
        if (!$propertyInfoType->isCollection()) {
            return null;
        }

        $simpleTypes = [];
        /** @psalm-suppress DeprecatedClass */
        foreach ($propertyInfoType->getCollectionValueTypes() as $collectionValueType) {
            $typeName = $collectionValueType->getBuiltinType() === SchemaType::Object->value && $collectionValueType->getClassName() !== null
                ? $collectionValueType->getClassName()
                : $collectionValueType->getBuiltinType();

            if ($typeName === '') {
                throw new InvalidTypeException();
            }

            $simpleTypes[] = new SimpleType(
                name: $this->getEnumTypeName($typeName),
                builtin: $this->getTypeBuildIn($typeName),
                enum: $this->getEnumValues($typeName),
            );
        }

        return new Type(types: $simpleTypes);
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
