<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ApiResponse;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ConfigurableObject;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\DatabaseConfig;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\LocalizableContent;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Product;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ServiceConfiguration;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\User;
use Testo\Assert;
use Testo\Test;

#[Test]
final class AdditionalPropertiesIntegrationTest
{
    public function testGenerateConfigurableObjectWithAdditionalProperties(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ConfigurableObject::class);
        $result = $schema->jsonSerialize();

        // Test basic required properties
        Assert::array($result)->hasKeys('properties');
        Assert::array($result['properties'])->hasKeys('name');
        Assert::array($result['properties'])->hasKeys('version');
        Assert::equals($result['properties']['name']['type'], 'string');
        Assert::equals($result['properties']['version']['type'], 'integer');

        // Test string additional properties
        Assert::array($result['properties'])->hasKeys('stringOptions');
        $expected = [
            'description' => 'String-based configuration options',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['stringOptions'], $expected);

        // Test numeric additional properties
        Assert::array($result['properties'])->hasKeys('numericSettings');
        $expected = [
            'description' => 'Numeric configuration values',
            'type' => 'object',
            'additionalProperties' => ['type' => 'integer'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['numericSettings'], $expected);

        // Test mixed additional properties
        Assert::array($result['properties'])->hasKeys('dynamicData');
        $expected = [
            'description' => 'Dynamic data of any type',
            'type' => 'object',
            'additionalProperties' => true,
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['dynamicData'], $expected);

        // Check required properties
        Assert::array($result)->hasKeys('required');
        Assert::contains($result['required'], 'name');
        Assert::contains($result['required'], 'version');
        Assert::iterable($result['required'])->notContains('stringOptions');
        Assert::iterable($result['required'])->notContains('numericSettings');
        Assert::iterable($result['required'])->notContains('dynamicData');
    }

    public function testGenerateServiceConfigurationWithObjectReferences(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ServiceConfiguration::class);
        $result = $schema->jsonSerialize();

        // Test basic properties
        Assert::array($result)->hasKeys('properties');
        Assert::equals($result['properties']['serviceName']['type'], 'string');
        Assert::equals($result['properties']['version']['type'], 'string');

        // Test object additional properties with class reference
        Assert::array($result['properties'])->hasKeys('endpoints');
        $expected = [
            'description' => 'Service endpoints configuration',
            'type' => 'object',
            'additionalProperties' => ['$ref' => '#/definitions/ServiceEndpoint'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['endpoints'], $expected);

        // Test integer additional properties
        Assert::array($result['properties'])->hasKeys('featureFlags');
        $expected = [
            'description' => 'Feature flags and numeric settings',
            'type' => 'object',
            'additionalProperties' => ['type' => 'integer'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['featureFlags'], $expected);

        // Test string additional properties
        Assert::array($result['properties'])->hasKeys('environment');
        $expected = [
            'description' => 'Environment variables',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['environment'], $expected);

        // Check that ServiceEndpoint definition is included
        Assert::array($result)->hasKeys('definitions');
        Assert::array($result['definitions'])->hasKeys('ServiceEndpoint');

        $serviceEndpointDef = $result['definitions']['ServiceEndpoint'];
        Assert::equals($serviceEndpointDef['type'], 'object');
        Assert::array($serviceEndpointDef)->hasKeys('properties');
        Assert::array($serviceEndpointDef['properties'])->hasKeys('url');
        Assert::array($serviceEndpointDef['properties'])->hasKeys('timeoutMs');
        Assert::array($serviceEndpointDef['properties'])->hasKeys('maxRetries');
        Assert::array($serviceEndpointDef['properties'])->hasKeys('enabled');

        // Check required properties for main object
        Assert::array($result)->hasKeys('required');
        Assert::contains($result['required'], 'serviceName');
        Assert::contains($result['required'], 'version');

        // Check required properties for ServiceEndpoint
        Assert::array($serviceEndpointDef)->hasKeys('required');
        Assert::contains($serviceEndpointDef['required'], 'url');
        Assert::contains($serviceEndpointDef['required'], 'timeoutMs');
        Assert::contains($serviceEndpointDef['required'], 'maxRetries');
        Assert::contains($serviceEndpointDef['required'], 'enabled');
    }

    public function testAdditionalPropertiesWithOtherConstraints(): void
    {
        // Test that additional properties work alongside other constraint attributes
        $generator = new Generator();
        $schema = $generator->generate(ConfigurableObject::class);
        $result = $schema->jsonSerialize();

        // Verify that Field descriptions are preserved along with additionalProperties
        Assert::equals($result['properties']['stringOptions']['description'], 'String-based configuration options');
        Assert::equals($result['properties']['numericSettings']['description'], 'Numeric configuration values');
        Assert::equals($result['properties']['dynamicData']['description'], 'Dynamic data of any type');
    }

    public function testDatabaseConfigurationExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(DatabaseConfig::class);
        $result = $schema->jsonSerialize();

        // Test basic properties
        Assert::equals($result['properties']['host']['type'], 'string');
        Assert::equals($result['properties']['port']['type'], 'integer');

        // Test additional properties with string type
        Assert::array($result['properties'])->hasKeys('driverOptions');
        $expected = [
            'description' => 'Driver-specific configuration options',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['driverOptions'], $expected);
    }

    public function testUserManagementExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(User::class);
        $result = $schema->jsonSerialize();

        // Test mixed type additional properties
        Assert::array($result['properties'])->hasKeys('attributes');
        $expected = [
            'description' => 'Custom user attributes and metadata',
            'type' => 'object',
            'additionalProperties' => true, // mixed type becomes true
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['attributes'], $expected);

        // Test string additional properties
        Assert::array($result['properties'])->hasKeys('preferences');
        $expected = [
            'description' => 'User preferences',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['preferences'], $expected);
    }

    public function testLocalizationExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(LocalizableContent::class);
        $result = $schema->jsonSerialize();

        // Test string additional properties for translations
        Assert::array($result['properties'])->hasKeys('translations');
        $expected = [
            'description' => 'Translations for different locales',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['translations'], $expected);

        // Test object additional properties with class reference
        Assert::array($result['properties'])->hasKeys('localeMetadata');
        $expected = [
            'description' => 'Locale-specific metadata',
            'type' => 'object',
            'additionalProperties' => ['$ref' => '#/definitions/LocaleMetadata'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['localeMetadata'], $expected);

        // Verify LocaleMetadata definition is included
        Assert::array($result)->hasKeys('definitions');
        Assert::array($result['definitions'])->hasKeys('LocaleMetadata');
    }

    public function testApiResponseExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ApiResponse::class);
        $result = $schema->jsonSerialize();

        // Test mixed additional properties for dynamic data
        Assert::array($result['properties'])->hasKeys('data');
        $expected = [
            'description' => 'Dynamic response data',
            'type' => 'object',
            'additionalProperties' => true,
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['data'], $expected);

        // Test string additional properties for headers
        Assert::array($result['properties'])->hasKeys('headers');
        $expected = [
            'description' => 'Response headers',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['headers'], $expected);
    }

    public function testEcommerceProductExample(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Product::class);
        $result = $schema->jsonSerialize();

        // Test price has Range constraint
        Assert::array($result['properties']['price'])->hasKeys('minimum');
        Assert::equals($result['properties']['price']['minimum'], 0);

        // Test mixed additional properties for attributes
        Assert::array($result['properties'])->hasKeys('attributes');
        $expected = [
            'description' => 'Product-specific attributes that vary by category',
            'type' => 'object',
            'additionalProperties' => true,
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['attributes'], $expected);

        // Test string additional properties for metadata
        Assert::array($result['properties'])->hasKeys('metadata');
        $expected = [
            'description' => 'SEO and marketing metadata',
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
            'default' => new \stdClass(),
        ];
        Assert::equals($result['properties']['metadata'], $expected);
    }

    public function testAdditionalPropertiesIntegrationWithExistingConstraints(): void
    {
        // Test that AdditionalProperties works alongside existing validation attributes
        $generator = new Generator();
        $schema = $generator->generate(Product::class);
        $result = $schema->jsonSerialize();

        // Verify that Field descriptions are preserved
        Assert::equals($result['properties']['attributes']['description'], 'Product-specific attributes that vary by category');

        // Verify that Range constraints are preserved on other fields
        Assert::equals($result['properties']['price']['minimum'], 0);

        // Verify that required properties work correctly
        Assert::contains($result['required'], 'id');
        Assert::contains($result['required'], 'name');
        Assert::contains($result['required'], 'price');
        Assert::iterable($result['required'])->notContains('attributes'); // has default value
        Assert::iterable($result['required'])->notContains('metadata'); // has default value
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
        Assert::equals($result['properties']['integers']['additionalProperties'], ['type' => 'integer']);

        // float should map to JSON schema 'number'
        Assert::equals($result['properties']['floats']['additionalProperties'], ['type' => 'number']);

        // bool should map to JSON schema 'boolean'
        Assert::equals($result['properties']['booleans']['additionalProperties'], ['type' => 'boolean']);
    }
}
