<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ValidatedUser;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ValidationIntegrationTest
{
    public function testGenerateValidatedUser(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);

        $result = $schema->jsonSerialize();

        // Test positive-int validation for age
        Assert::equals($result['properties']['age'], [
            'title' => 'Age',
            'description' => 'User age',
            'type' => 'integer',
            'minimum' => 1, // from positive-int
        ]);

        // Test int range validation for score
        Assert::equals($result['properties']['score'], [
            'title' => 'Score',
            'description' => 'User score between 0 and 100',
            'type' => 'integer',
            'minimum' => 0, // from int<0, 100>
            'maximum' => 100,
        ]);

        // Test non-empty-string validation for name
        Assert::equals($result['properties']['name'], [
            'title' => 'Name',
            'description' => 'User full name',
            'type' => 'string',
            'minLength' => 1, // from non-empty-string
        ]);

        // Test numeric-string validation for phone
        Assert::equals($result['properties']['phone'], [
            'title' => 'Phone Number',
            'description' => 'Numeric phone number',
            'type' => 'string',
            'pattern' => '^[0-9]*\.?[0-9]+$', // from numeric-string
        ]);

        // Test non-empty-array validation for tags (account for default value)
        $expected = [
            'title' => 'Tags',
            'description' => 'User tags',
            'type' => 'array',
            'items' => ['type' => 'string'],
            'minItems' => 1, // from non-empty-array
            'default' => [], // from default value in constructor
        ];

        Assert::equals($result['properties']['tags'], $expected);

        // Test array shape validation for preferences (account for default value)
        $preferencesExpected = [
            'title' => 'Preferences',
            'description' => 'User preferences',
            'type' => 'object',
            'properties' => [
                'theme' => ['type' => 'string'],
                'notifications' => ['type' => 'boolean'],
            ],
            'required' => ['theme', 'notifications'],
            'additionalProperties' => false, // from array shape constraint
            'default' => [], // from default value in constructor
        ];

        Assert::equals($result['properties']['preferences'], $preferencesExpected);
    }

    public function testRequiredProperties(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);

        $result = $schema->jsonSerialize();

        Assert::array($result)->hasKeys('required');
        Assert::contains($result['required'], 'name');
        Assert::contains($result['required'], 'age');
        Assert::contains($result['required'], 'score');
        Assert::contains($result['required'], 'email');
        Assert::contains($result['required'], 'phone');

        // tags and preferences have default values, so they shouldn't be required
        Assert::iterable($result['required'])->notContains('tags');
        Assert::iterable($result['required'])->notContains('preferences');
    }

    public function testValidationConstraintDetails(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);

        $result = $schema->jsonSerialize();

        // Verify minimum constraint on age (positive-int)
        Assert::array($result['properties']['age'])->hasKeys('minimum');
        Assert::equals($result['properties']['age']['minimum'], 1);

        // Verify range constraints on score (int<0, 100>)
        Assert::array($result['properties']['score'])->hasKeys('minimum');
        Assert::array($result['properties']['score'])->hasKeys('maximum');
        Assert::equals($result['properties']['score']['minimum'], 0);
        Assert::equals($result['properties']['score']['maximum'], 100);

        // Verify string length constraint on name (non-empty-string)
        Assert::array($result['properties']['name'])->hasKeys('minLength');
        Assert::equals($result['properties']['name']['minLength'], 1);

        // Verify pattern constraint on phone (numeric-string)
        Assert::array($result['properties']['phone'])->hasKeys('pattern');
        Assert::equals($result['properties']['phone']['pattern'], '^[0-9]*\.?[0-9]+$');

        // Verify array constraint on tags (non-empty-array)
        Assert::array($result['properties']['tags'])->hasKeys('minItems');
        Assert::equals($result['properties']['tags']['minItems'], 1);

        // Verify object structure for preferences (array shape)
        Assert::equals($result['properties']['preferences']['type'], 'object');
        Assert::array($result['properties']['preferences'])->hasKeys('properties');
        Assert::array($result['properties']['preferences'])->hasKeys('required');
        Assert::false($result['properties']['preferences']['additionalProperties']);
    }
}
