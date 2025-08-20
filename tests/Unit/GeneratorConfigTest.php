<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\GeneratorConfig;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ValidatedUser;

final class GeneratorConfigTest extends TestCase
{
    public function testValidationConstraintsEnabled(): void
    {
        $config = new GeneratorConfig(enableValidationConstraints: true);
        $generator = new Generator(config: $config);

        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should have validation constraints
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertEquals(1, $result['properties']['age']['minimum']);

        $this->assertArrayHasKey('minLength', $result['properties']['name']);
        $this->assertEquals(1, $result['properties']['name']['minLength']);
    }

    public function testValidationConstraintsDisabled(): void
    {
        $config = new GeneratorConfig(enableValidationConstraints: false);
        $generator = new Generator(config: $config);

        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should NOT have validation constraints
        $this->assertArrayNotHasKey('minimum', $result['properties']['age']);
        $this->assertArrayNotHasKey('minLength', $result['properties']['name']);

        // But should still have basic type information
        $this->assertEquals('integer', $result['properties']['age']['type']);
        $this->assertEquals('string', $result['properties']['name']['type']);
    }

    public function testDefaultConfigurationEnablesValidation(): void
    {
        $config = new GeneratorConfig(); // Default: enableValidationConstraints = true
        $generator = new Generator(config: $config);

        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should have validation constraints by default
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertEquals(1, $result['properties']['age']['minimum']);
    }

    public function testBackwardCompatibilityWithoutConfig(): void
    {
        $generator = new Generator(); // No config provided - should use default

        $schema = $generator->generate(ValidatedUser::class);
        $result = $schema->jsonSerialize();

        // Should have validation constraints (backward compatible)
        $this->assertArrayHasKey('minimum', $result['properties']['age']);
        $this->assertEquals(1, $result['properties']['age']['minimum']);
    }

    public function testConfigurationProperties(): void
    {
        $config = new GeneratorConfig(
            enableValidationConstraints: false,
        );

        $this->assertFalse($config->enableValidationConstraints);
    }

    public function testPerformanceWithDisabledConstraints(): void
    {
        // Test that disabling constraints doesn't break existing functionality
        $config = new GeneratorConfig(enableValidationConstraints: false);
        $generator = new Generator(config: $config);

        // Use a class without PHPDoc constraints
        $schema = $generator->generate(Movie::class);
        $result = $schema->jsonSerialize();

        // Should work normally for non-constrained classes
        $this->assertEquals('object', $result['type']);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('title', $result['properties']);
    }
}
