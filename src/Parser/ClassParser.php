<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Exception\GeneratorException;
use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\TypeInfo\Type as TypeInfoType;

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

    private readonly PropertyTypeExtractorInterface $propertyInfo;

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

    private function getPropertyType(\ReflectionProperty $property): Type
    {
        $type = $this->propertyInfo->getType($property->class, $property->getName());

        if ($type === null) {
            throw new InvalidTypeException();
        }

        return $this->createType($type);
    }

    private function createType(TypeInfoType $type): Type
    {
        $simpleTypes = [];
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $subType) {
                $simpleType = $this->createSimpleType($subType);
                if ($simpleType !== null) {
                    $simpleTypes[] = $simpleType;
                }
            }
        } else {
            $simpleType = $this->createSimpleType($type);
            if ($simpleType !== null) {
                $simpleTypes[] = $simpleType;
            }
        }

        return new Type(types: $simpleTypes);
    }

    private function createSimpleType(TypeInfoType $type): ?SimpleType
    {
        $typeName = '';
        $builtin = true;
        $enum = null;
        $collectionType = null;
        if ($type instanceof BuiltinType) {
            if ($type->getTypeIdentifier() === TypeIdentifier::MIXED) {
                return null;
            }
            $typeName = $type->getTypeIdentifier()->value;
        }
        if ($type instanceof CollectionType) {
            $typeName = TypeIdentifier::ARRAY->value;
            $collectionType = $this->createType($type->getCollectionValueType());
        }
        if ($type instanceof ObjectType) {
            $typeName = $type->getClassName();
            $builtin = false;
        }

        if ($type instanceof BackedEnumType) {
            $enum = $this->getEnumValues($type->getClassName());
            $typeName = $type->getBackingType()->getTypeIdentifier()->value;
            $builtin = true;
        }

        if ($typeName === '') {
            throw new InvalidTypeException();
        }

        return new SimpleType(
            name: $typeName,
            builtin: $builtin,
            collectionType: $collectionType,
            enum: $enum,
        );
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

    private function createPropertyInfo(): PropertyTypeExtractorInterface
    {
        return new PropertyInfoExtractor(typeExtractors: [
            new PhpStanExtractor(),
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        ]);
    }
}
