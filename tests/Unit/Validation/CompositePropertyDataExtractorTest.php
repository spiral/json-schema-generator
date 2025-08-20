<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Parser\Property;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;
use Spiral\JsonSchemaGenerator\Validation\AttributeConstraintExtractor;
use Spiral\JsonSchemaGenerator\Validation\CompositePropertyDataExtractor;

final class CompositePropertyDataExtractorTest extends TestCase
{
    public function testCreateDefault(): void
    {
        $extractor = CompositePropertyDataExtractor::createDefault();

        $this->assertInstanceOf(CompositePropertyDataExtractor::class, $extractor);

        // Test that both extractors work by using a property with both attribute and PHPDoc constraints
        $testClass = new class {
            /** @var positive-int */
            #[\Spiral\JsonSchemaGenerator\Attribute\Constraint\Range(min: 1, max: 100)]
            public int $value;
        };

        $property = new \ReflectionProperty($testClass, 'value');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('int', true)]),
            hasDefaultValue: false,
        );

        $rules = $extractor->extractValidationRules($propertyWrapper, SchemaType::Integer);

        // Should have constraints from both extractors
        $this->assertArrayHasKey('minimum', $rules);
        $this->assertEquals(1, $rules['minimum']); // From both attribute and PHPDoc
        $this->assertArrayHasKey('maximum', $rules);
        $this->assertEquals(100, $rules['maximum']); // From attribute
    }

    public function testWithExtractor(): void
    {
        $extractor = (new CompositePropertyDataExtractor())
            ->withExtractor(new AttributeConstraintExtractor());

        $testClass = new class {
            #[\Spiral\JsonSchemaGenerator\Attribute\Constraint\Range(min: 5, max: 50)]
            public int $value;
        };

        $property = new \ReflectionProperty($testClass, 'value');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('int', true)]),
            hasDefaultValue: false,
        );

        $rules = $extractor->extractValidationRules($propertyWrapper, SchemaType::Integer);

        $this->assertEquals(['minimum' => 5, 'maximum' => 50], $rules);
    }
}
