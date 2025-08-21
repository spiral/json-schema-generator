<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ApiResponse;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ConfigurableObject;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\DatabaseConfig;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\LocalizableContent;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Product;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ServiceConfiguration;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\User;

final class AdditionalPropertiesIntegrationTest extends TestCase
{
    public function testGenerateConfigurableObjectWithAdditionalProperties(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ConfigurableObject::class);
        $result = $schema->jsonSerialize();

        // Test basic required properties
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('version', $result['properties']);
        $this->assertEquals('string', $result['properties']['name']['type']);
        $this->assertEquals('integer', $result['properties']['version']['type']);

        // Test string additional properties
        $this->assertArrayHasKey('stringOptions', $result['properties']);
        $expected = [
            'description' => 'String-based configuration options',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['stringOptions']);

        // Test numeric additional properties
        $this->assertArrayHasKey('numericSettings', $result['properties']);
        $expected = [
            'description' => 'Numeric configuration values',
            'type' => 'object',
            'additionalProperties' => ['type' => 'integer'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['numericSettings']);

        // Test mixed additional properties
        $this->assertArrayHasKey('dynamicData', $result['properties']);
        $expected = [
            'description' => 'Dynamic data of any type',
            'type' => 'object',
            'additionalProperties' => true,
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['dynamicData']);

        // Check required properties
        $this->assertArrayHasKey('required', $result);
        $this->assertContains('name', $result['required']);
        $this->assertContains('version', $result['required']);
        $this->assertNotContains('stringOptions', $result['required']);
        $this->assertNotContains('numericSettings', $result['required']);
        $this->assertNotContains('dynamicData', $result['required']);
    }

    public function testGenerateServiceConfigurationWithObjectReferences(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ServiceConfiguration::class);
        $result = $schema->jsonSerialize();

        // Test basic properties
        $this->assertArrayHasKey('properties', $result);
        $this->assertEquals('string', $result['properties']['serviceName']['type']);
        $this->assertEquals('string', $result['properties']['version']['type']);

        // Test object additional properties with class reference
        $this->assertArrayHasKey('endpoints', $result['properties']);
        $expected = [
            'description' => 'Service endpoints configuration',
            'type' => 'object',
            'additionalProperties' => ['$ref' => '#/definitions/ServiceEndpoint'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['endpoints']);

        // Test integer additional properties
        $this->assertArrayHasKey('featureFlags', $result['properties']);
        $expected = [
            'description' => 'Feature flags and numeric settings',
            'type' => 'object',
            'additionalProperties' => ['type' => 'integer'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['featureFlags']);

        // Test string additional properties
        $this->assertArrayHasKey('environment', $result['properties']);
        $expected = [
            'description' => 'Environment variables',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['environment']);

        // Check that ServiceEndpoint definition is included
        $this->assertArrayHasKey('definitions', $result);
        $this->assertArrayHasKey('ServiceEndpoint', $result['definitions']);

        $serviceEndpointDef = $result['definitions']['ServiceEndpoint'];
        $this->assertEquals('object', $serviceEndpointDef['type']);
        $this->assertArrayHasKey('properties', $serviceEndpointDef);
        $this->assertArrayHasKey('url', $serviceEndpointDef['properties']);
        $this->assertArrayHasKey('timeoutMs', $serviceEndpointDef['properties']);
        $this->assertArrayHasKey('maxRetries', $serviceEndpointDef['properties']);
        $this->assertArrayHasKey('enabled', $serviceEndpointDef['properties']);

        // Check required properties for main object
        $this->assertArrayHasKey('required', $result);
        $this->assertContains('serviceName', $result['required']);
        $this->assertContains('version', $result['required']);

        // Check required properties for ServiceEndpoint
        $this->assertArrayHasKey('required', $serviceEndpointDef);
        $this->assertContains('url', $serviceEndpointDef['required']);
        $this->assertContains('timeoutMs', $serviceEndpointDef['required']);
        $this->assertContains('maxRetries', $serviceEndpointDef['required']);
        $this->assertContains('enabled', $serviceEndpointDef['required']);
    }

    public function testAdditionalPropertiesWithOtherConstraints(): void
    {
        // Test that additional properties work alongside other constraint attributes
        $generator = new Generator();
        $schema = $generator->generate(ConfigurableObject::class);
        $result = $schema->jsonSerialize();

        // Verify that Field descriptions are preserved along with additionalProperties
        $this->assertEquals(
            'String-based configuration options',
            $result['properties']['stringOptions']['description'],
        );
        $this->assertEquals(
            'Numeric configuration values',
            $result['properties']['numericSettings']['description'],
        );
        $this->assertEquals(
            'Dynamic data of any type',
            $result['properties']['dynamicData']['description'],
        );
    }

    public function testDatabaseConfigurationExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(DatabaseConfig::class);
        $result = $schema->jsonSerialize();

        // Test basic properties
        $this->assertEquals('string', $result['properties']['host']['type']);
        $this->assertEquals('integer', $result['properties']['port']['type']);

        // Test additional properties with string type
        $this->assertArrayHasKey('driverOptions', $result['properties']);
        $expected = [
            'description' => 'Driver-specific configuration options',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['driverOptions']);
    }

    public function testUserManagementExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(User::class);
        $result = $schema->jsonSerialize();

        // Test mixed type additional properties
        $this->assertArrayHasKey('attributes', $result['properties']);
        $expected = [
            'description' => 'Custom user attributes and metadata',
            'type' => 'object',
            'additionalProperties' => true, // mixed type becomes true
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['attributes']);

        // Test string additional properties
        $this->assertArrayHasKey('preferences', $result['properties']);
        $expected = [
            'description' => 'User preferences',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['preferences']);
    }

    public function testLocalizationExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(LocalizableContent::class);
        $result = $schema->jsonSerialize();

        // Test string additional properties for translations
        $this->assertArrayHasKey('translations', $result['properties']);
        $expected = [
            'description' => 'Translations for different locales',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['translations']);

        // Test object additional properties with class reference
        $this->assertArrayHasKey('localeMetadata', $result['properties']);
        $expected = [
            'description' => 'Locale-specific metadata',
            'type' => 'object',
            'additionalProperties' => ['$ref' => '#/definitions/LocaleMetadata'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['localeMetadata']);

        // Verify LocaleMetadata definition is included
        $this->assertArrayHasKey('definitions', $result);
        $this->assertArrayHasKey('LocaleMetadata', $result['definitions']);
    }

    public function testApiResponseExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ApiResponse::class);
        $result = $schema->jsonSerialize();

        // Test mixed additional properties for dynamic data
        $this->assertArrayHasKey('data', $result['properties']);
        $expected = [
            'description' => 'Dynamic response data',
            'type' => 'object',
            'additionalProperties' => true,
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['data']);

        // Test string additional properties for headers
        $this->assertArrayHasKey('headers', $result['properties']);
        $expected = [
            'description' => 'Response headers',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['headers']);
    }

    public function testEcommerceProductExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Product::class);
        $result = $schema->jsonSerialize();

        // Test price has Range constraint
        $this->assertArrayHasKey('minimum', $result['properties']['price']);
        $this->assertEquals(0, $result['properties']['price']['minimum']);

        // Test mixed additional properties for attributes
        $this->assertArrayHasKey('attributes', $result['properties']);
        $expected = [
            'description' => 'Product-specific attributes that vary by category',
            'type' => 'object',
            'additionalProperties' => true,
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['attributes']);

        // Test string additional properties for metadata
        $this->assertArrayHasKey('metadata', $result['properties']);
        $expected = [
            'description' => 'SEO and marketing metadata',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => [],
        ];
        $this->assertEquals($expected, $result['properties']['metadata']);
    }

    public function testAdditionalPropertiesIntegrationWithExistingConstraints(): void
    {
        // Test that AdditionalProperties works alongside existing validation attributes
        $generator = new Generator();
        $schema = $generator->generate(Product::class);
        $result = $schema->jsonSerialize();

        // Verify that Field descriptions are preserved
        $this->assertEquals(
            'Product-specific attributes that vary by category',
            $result['properties']['attributes']['description'],
        );

        // Verify that Range constraints are preserved on other fields
        $this->assertEquals(0, $result['properties']['price']['minimum']);

        // Verify that required properties work correctly
        $this->assertContains('id', $result['required']);
        $this->assertContains('name', $result['required']);
        $this->assertContains('price', $result['required']);
        $this->assertNotContains('attributes', $result['required']); // has default value
        $this->assertNotContains('metadata', $result['required']); // has default value
    }

    public function testTypeAlternatives(): void
    {
        // Test that different type names work correctly
        $testClass = new class {
            #[AdditionalProperties(valueType: 'integer')]
            public array $integers = [];

            #[AdditionalProperties(valueType: 'float')]
            public array $floats = [];

            #[AdditionalProperties(valueType: 'bool')]
            public array $booleans = [];
        };

        $generator = new Generator();
        $schema = $generator->generate($testClass::class);
        $result = $schema->jsonSerialize();

        // integer should map to JSON schema 'integer'
        $this->assertEquals(['type' => 'integer'], $result['properties']['integers']['additionalProperties']);

        // float should map to JSON schema 'number'
        $this->assertEquals(['type' => 'number'], $result['properties']['floats']['additionalProperties']);

        // bool should map to JSON schema 'boolean'
        $this->assertEquals(['type' => 'boolean'], $result['properties']['booleans']['additionalProperties']);
    }
}
