<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ValidatedUser;

final class ValidationIntegrationTest extends TestCase
{
    public function testGenerateValidatedUser(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);

        $result = $schema->jsonSerialize();

        // Test positive-int validation for age
        $this->assertEquals(
            [
                'title' => 'Age',
                'description' => 'User age',
                'type' => 'integer',
                'minimum' => 1, // from positive-int
            ],
            $result['properties']['age'],
        );

        // Test int range validation for score
        $this->assertEquals(
            [
                'title' => 'Score',
                'description' => 'User score between 0 and 100',
                'type' => 'integer',
                'minimum' => 0, // from int<0, 100>
                'maximum' => 100,
            ],
            $result['properties']['score'],
        );

        // Test non-empty-string validation for name
        $this->assertEquals(
            [
                'title' => 'Name',
                'description' => 'User full name',
                'type' => 'string',
                'minLength' => 1, // from non-empty-string
            ],
            $result['properties']['name'],
        );

        // Test numeric-string validation for phone
        $this->assertEquals(
            [
                'title' => 'Phone Number',
                'description' => 'Numeric phone number',
                'type' => 'string',
                'pattern' => '^[0-9]*\.?[0-9]+$', // from numeric-string
            ],
            $result['properties']['phone'],
        );

        // Test non-empty-array validation for tags (account for default value)
        $expected = [
            'title' => 'Tags',
            'description' => 'User tags',
            'type' => 'array',
            'items' => ['type' => 'string'],
            'minItems' => 1, // from non-empty-array
            'default' => [], // from default value in constructor
        ];

        $this->assertEquals($expected, $result['properties']['tags']);

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

        $this->assertEquals($preferencesExpected, $result['properties']['preferences']);
    }

    public function testRequiredProperties(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);

        $result = $schema->jsonSerialize();

        $this->assertArrayHasKey('required', $result);
        $this->assertContains('name', $result['required']);
        $this->assertContains('age', $result['required']);
        $this->assertContains('score', $result['required']);
        $this->assertContains('email', $result['required']);
        $this->assertContains('phone', $result['required']);

        // tags and preferences have default values, so they shouldn't be required
        $this->assertNotContains('tags', $result['required']);
        $this->assertNotContains('preferences', $result['required']);
    }

    public function testValidationConstraintDetails(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ValidatedUser::class);

        $result = $schema->jsonSerialize();

        // Verify minimum constraint on age (positive-int)
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertEquals(1, $result['properties']['age']['minimum']);

        // Verify range constraints on score (int<0, 100>)
        $this->assertArrayHasKey('minimum', $result['properties']['score']);
        $this->assertArrayHasKey('maximum', $result['properties']['score']);
        $this->assertEquals(0, $result['properties']['score']['minimum']);
        $this->assertEquals(100, $result['properties']['score']['maximum']);

        // Verify string length constraint on name (non-empty-string)
        $this->assertArrayHasKey('minLength', $result['properties']['name']);
        $this->assertEquals(1, $result['properties']['name']['minLength']);

        // Verify pattern constraint on phone (numeric-string)
        $this->assertArrayHasKey('pattern', $result['properties']['phone']);
        $this->assertEquals('^[0-9]*\.?[0-9]+$', $result['properties']['phone']['pattern']);

        // Verify array constraint on tags (non-empty-array)
        $this->assertArrayHasKey('minItems', $result['properties']['tags']);
        $this->assertEquals(1, $result['properties']['tags']['minItems']);

        // Verify object structure for preferences (array shape)
        $this->assertEquals('object', $result['properties']['preferences']['type']);
        $this->assertArrayHasKey('properties', $result['properties']['preferences']);
        $this->assertArrayHasKey('required', $result['properties']['preferences']);
        $this->assertFalse($result['properties']['preferences']['additionalProperties']);
    }
}
