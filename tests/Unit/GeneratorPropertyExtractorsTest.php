<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\UserWithConstraints;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ValidatedUser;
use Spiral\JsonSchemaGenerator\Validation\AttributeConstraintExtractor;
use Spiral\JsonSchemaGenerator\Validation\CompositePropertyDataExtractor;
use Spiral\JsonSchemaGenerator\Validation\PhpDocValidationConstraintExtractor;
use Testo\Assert;
use Testo\Test;

#[Test]
final class GeneratorPropertyExtractorsTest
{
    public function testCreateDefault(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(UserWithConstraints::class);
        $result = $schema->jsonSerialize();

        // Should have both attribute and PHPDoc-based constraints
        Assert::array($result)->hasKeys('properties');
        Assert::array($result['properties'])->hasKeys('age');

        // Age should have range constraint from attribute
        Assert::array($result['properties']['age'])->hasKeys('minimum');
        Assert::array($result['properties']['age'])->hasKeys('maximum');
        Assert::equals($result['properties']['age']['minimum'], 13);
        Assert::equals($result['properties']['age']['maximum'], 120);
    }

    public function testCreateWithValidationOnly(): void
    {
        $generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([
            new PhpDocValidationConstraintExtractor(),
        ]));
        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should have PHPDoc-based constraints but not attribute constraints
        Assert::array($result['properties']['age'])->hasKeys('minimum');
        Assert::equals($result['properties']['age']['minimum'], 1); // from positive-int

        // Should have minLength from non-empty-string
        Assert::array($result['properties']['name'])->hasKeys('minLength');
        Assert::equals($result['properties']['name']['minLength'], 1);
    }

    public function testCreateWithAttributesOnly(): void
    {
        $generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([
            new AttributeConstraintExtractor(),
        ]));

        $schema = $generator->generate(UserWithConstraints::class);
        $result = $schema->jsonSerialize();

        // Should have attribute-based constraints
        Assert::array($result['properties']['age'])->hasKeys('minimum');
        Assert::equals($result['properties']['age']['minimum'], 13);

        // Should have pattern from Pattern attribute
        Assert::array($result['properties']['name'])->hasKeys('pattern');
        Assert::equals($result['properties']['name']['pattern'], '^[A-Z][a-z]+(?: [A-Z][a-z]+)*$');
    }

    public function testCreateWithoutValidation(): void
    {
        $generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([]));

        $schema = $generator->generate(UserWithConstraints::class);
        $result = $schema->jsonSerialize();

        // Should not have any validation constraints
        Assert::array($result['properties']['age'])->doesNotHaveKeys('minimum');
        Assert::array($result['properties']['age'])->doesNotHaveKeys('maximum');
        Assert::array($result['properties']['name'])->doesNotHaveKeys('pattern');
        Assert::array($result['properties']['name'])->doesNotHaveKeys('minLength');
        Assert::array($result['properties']['name'])->doesNotHaveKeys('maxLength');

        // But should still have basic type information
        Assert::equals($result['properties']['age']['type'], 'integer');
        Assert::equals($result['properties']['name']['type'], 'string');
    }

    public function testBackwardCompatibility(): void
    {
        // Old style constructor should still work with default behavior
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should have validation constraints (backward compatible)
        Assert::array($result['properties']['age'])->hasKeys('minimum');
        Assert::equals($result['properties']['age']['minimum'], 1);
    }
}
