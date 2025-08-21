<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Parser\Property;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;
use Spiral\JsonSchemaGenerator\Validation\AdditionalPropertiesExtractor;

final class AdditionalPropertiesExtractorTest extends TestCase
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

        $this->assertEquals($expected, $rules);
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

        $this->assertEquals($expected, $rules);
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

        $this->assertEquals($expected, $rules);
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

        $this->assertEquals($expected, $rules);
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

        $this->assertEquals($expected, $rules);
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

        $this->assertEquals($expected, $rules);
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

        $this->assertEquals([], $rules);
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

        $this->assertEquals([], $rules);
    }

    protected function setUp(): void
    {
        $this->extractor = new AdditionalPropertiesExtractor();
    }
}
