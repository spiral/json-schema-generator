<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Parser\Property;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;
use Spiral\JsonSchemaGenerator\Validation\AdditionalPropertiesExtractor;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class AdditionalPropertiesExtractorTest
{
    private AdditionalPropertiesExtractor $extractor;

    public function testExtractAdditionalPropertiesWithStringType(): void
    {
        $testClass = new class {
            #[AdditionalProperties(valueType: 'string')]
            public array $metadata = [];
        };

        $property = new \ReflectionProperty($testClass, 'metadata');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: true,
            defaultValue: [],
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        $expected = [
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
        ];

        Assert::equals($rules, $expected);
    }

    public function testExtractAdditionalPropertiesWithIntegerType(): void
    {
        $testClass = new class {
            #[AdditionalProperties(valueType: 'int')]
            public array $counters = [];
        };

        $property = new \ReflectionProperty($testClass, 'counters');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: true,
            defaultValue: [],
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        $expected = [
            'type' => 'object',
            'additionalProperties' => ['type' => 'integer'],
        ];

        Assert::equals($rules, $expected);
    }

    public function testExtractAdditionalPropertiesWithNumberType(): void
    {
        $testClass = new class {
            #[AdditionalProperties(valueType: 'number')]
            public array $measurements = [];
        };

        $property = new \ReflectionProperty($testClass, 'measurements');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: true,
            defaultValue: [],
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        $expected = [
            'type' => 'object',
            'additionalProperties' => ['type' => 'number'],
        ];

        Assert::equals($rules, $expected);
    }

    public function testExtractAdditionalPropertiesWithBooleanType(): void
    {
        $testClass = new class {
            #[AdditionalProperties(valueType: 'boolean')]
            public array $flags = [];
        };

        $property = new \ReflectionProperty($testClass, 'flags');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: true,
            defaultValue: [],
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        $expected = [
            'type' => 'object',
            'additionalProperties' => ['type' => 'boolean'],
        ];

        Assert::equals($rules, $expected);
    }

    public function testExtractAdditionalPropertiesWithMixedType(): void
    {
        $testClass = new class {
            #[AdditionalProperties(valueType: 'mixed')]
            public array $dynamicData = [];
        };

        $property = new \ReflectionProperty($testClass, 'dynamicData');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: true,
            defaultValue: [],
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        $expected = [
            'type' => 'object',
            'additionalProperties' => true,
        ];

        Assert::equals($rules, $expected);
    }

    public function testExtractAdditionalPropertiesWithObjectType(): void
    {
        $testClass = new class {
            #[AdditionalProperties(valueType: 'object', valueClass: \stdClass::class)]
            public array $objects = [];
        };

        $property = new \ReflectionProperty($testClass, 'objects');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: true,
            defaultValue: [],
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        $expected = [
            'type' => 'object',
            'additionalProperties' => ['$ref' => '#/definitions/stdClass'],
            '_additionalPropertiesClass' => \stdClass::class, // Include the internal metadata
        ];

        Assert::equals($rules, $expected);
    }

    public function testExtractAdditionalPropertiesIgnoredForNonArrayTypes(): void
    {
        $testClass = new class {
            #[AdditionalProperties(valueType: 'string')]
            public string $notAnArray;
        };

        $property = new \ReflectionProperty($testClass, 'notAnArray');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, []);
    }

    public function testExtractAdditionalPropertiesNotPresent(): void
    {
        $testClass = new class {
            public array $plainArray = [];
        };

        $property = new \ReflectionProperty($testClass, 'plainArray');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: true,
            defaultValue: [],
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        Assert::equals($rules, []);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->extractor = new AdditionalPropertiesExtractor();
    }
}
