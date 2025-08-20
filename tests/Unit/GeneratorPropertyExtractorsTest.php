<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\UserWithConstraints;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ValidatedUser;
use Spiral\JsonSchemaGenerator\Validation\AttributeConstraintExtractor;
use Spiral\JsonSchemaGenerator\Validation\CompositePropertyDataExtractor;
use Spiral\JsonSchemaGenerator\Validation\PhpDocValidationConstraintExtractor;

final class GeneratorPropertyExtractorsTest extends TestCase
{
    public function testCreateDefault(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(UserWithConstraints::class);
        $result = $schema->jsonSerialize();

        // Should have both attribute and PHPDoc-based constraints
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('age', $result['properties']);

        // Age should have range constraint from attribute
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertArrayHasKey('maximum', $result['properties']['age']);
        $this->assertEquals(13, $result['properties']['age']['minimum']);
        $this->assertEquals(120, $result['properties']['age']['maximum']);
    }

    public function testCreateWithValidationOnly(): void
    {
        $generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([
            new PhpDocValidationConstraintExtractor(),
        ]));
        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should have PHPDoc-based constraints but not attribute constraints
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertEquals(1, $result['properties']['age']['minimum']); // from positive-int

        // Should have minLength from non-empty-string
        $this->assertArrayHasKey('minLength', $result['properties']['name']);
        $this->assertEquals(1, $result['properties']['name']['minLength']);
    }

    public function testCreateWithAttributesOnly(): void
    {
        $generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([
            new AttributeConstraintExtractor(),
        ]));

        $schema = $generator->generate(UserWithConstraints::class);
        $result = $schema->jsonSerialize();

        // Should have attribute-based constraints
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertEquals(13, $result['properties']['age']['minimum']);

        // Should have pattern from Pattern attribute
        $this->assertArrayHasKey('pattern', $result['properties']['name']);
        $this->assertEquals('^[A-Z][a-z]+(?: [A-Z][a-z]+)*$', $result['properties']['name']['pattern']);
    }

    public function testCreateWithoutValidation(): void
    {
        $generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([]));

        $schema = $generator->generate(UserWithConstraints::class);
        $result = $schema->jsonSerialize();

        // Should not have any validation constraints
        $this->assertArrayNotHasKey('minimum', $result['properties']['age']);
        $this->assertArrayNotHasKey('maximum', $result['properties']['age']);
        $this->assertArrayNotHasKey('pattern', $result['properties']['name']);
        $this->assertArrayNotHasKey('minLength', $result['properties']['name']);
        $this->assertArrayNotHasKey('maxLength', $result['properties']['name']);

        // But should still have basic type information
        $this->assertEquals('integer', $result['properties']['age']['type']);
        $this->assertEquals('string', $result['properties']['name']['type']);
    }

    public function testBackwardCompatibility(): void
    {
        // Old style constructor should still work with default behavior
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should have validation constraints (backward compatible)
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertEquals(1, $result['properties']['age']['minimum']);
    }
}
