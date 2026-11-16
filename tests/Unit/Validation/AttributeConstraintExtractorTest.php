<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Enum;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Items;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\MultipleOf;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Pattern;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;
use Spiral\JsonSchemaGenerator\Parser\Property;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;
use Spiral\JsonSchemaGenerator\Validation\AttributeConstraintExtractor;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class AttributeConstraintExtractorTest
{
    private AttributeConstraintExtractor $extractor;

    public function testExtractPatternConstraint(): void
    {
        $testClass = new class {
            #[Pattern('^[a-zA-Z]+$')]
            public string $name;
        };

        $property = new \ReflectionProperty($testClass, 'name');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, ['pattern' => '^[a-zA-Z]+$']);
    }

    public function testExtractLengthConstraintForString(): void
    {
        $testClass = new class {
            #[Length(min: 3, max: 20)]
            public string $username;
        };

        $property = new \ReflectionProperty($testClass, 'username');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, ['minLength' => 3, 'maxLength' => 20]);
    }

    public function testExtractLengthConstraintForArray(): void
    {
        $testClass = new class {
            #[Length(min: 1, max: 5)]
            public array $items;
        };

        $property = new \ReflectionProperty($testClass, 'items');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        Assert::equals($rules, ['minItems' => 1, 'maxItems' => 5]);
    }

    public function testExtractRangeConstraint(): void
    {
        $testClass = new class {
            #[Range(min: 0, max: 100)]
            public int $score;
        };

        $property = new \ReflectionProperty($testClass, 'score');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('int', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Integer);

        Assert::equals($rules, ['minimum' => 0, 'maximum' => 100]);
    }

    public function testExtractRangeConstraintWithExclusiveBounds(): void
    {
        $testClass = new class {
            #[Range(min: 0, max: 100, exclusiveMin: true, exclusiveMax: true)]
            public float $value;
        };

        $property = new \ReflectionProperty($testClass, 'value');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('float', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Number);

        Assert::equals($rules, [
            'exclusiveMinimum' => 0,
            'exclusiveMaximum' => 100,
        ]);
    }

    public function testExtractMultipleOfConstraintForInteger(): void
    {
        $testClass = new class {
            #[MultipleOf(5)]
            public int $number;
        };

        $property = new \ReflectionProperty($testClass, 'number');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('int', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Integer);

        Assert::equals($rules, ['multipleOf' => 5]);
    }

    public function testExtractMultipleOfConstraintIgnoredForString(): void
    {
        $testClass = new class {
            #[MultipleOf(5)]
            public string $text;
        };

        $property = new \ReflectionProperty($testClass, 'text');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, []);
    }

    public function testExtractItemsConstraint(): void
    {
        $testClass = new class {
            #[Items(min: 2, max: 10, unique: true)]
            public array $tags;
        };

        $property = new \ReflectionProperty($testClass, 'tags');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('array', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::Array);

        Assert::equals($rules, [
            'minItems' => 2,
            'maxItems' => 10,
            'uniqueItems' => true,
        ]);
    }

    public function testExtractItemsConstraintIgnoredForString(): void
    {
        $testClass = new class {
            #[Items(min: 1)]
            public string $text;
        };

        $property = new \ReflectionProperty($testClass, 'text');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, []);
    }

    public function testExtractEnumConstraint(): void
    {
        $testClass = new class {
            #[Enum(['active', 'inactive', 'pending'])]
            public string $status;
        };

        $property = new \ReflectionProperty($testClass, 'status');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, ['enum' => ['active', 'inactive', 'pending']]);
    }

    public function testExtractMultipleConstraints(): void
    {
        $testClass = new class {
            #[Pattern('^[a-zA-Z]+$')]
            #[Length(min: 3, max: 20)]
            #[Enum(['admin', 'user', 'guest'])]
            public string $role;
        };

        $property = new \ReflectionProperty($testClass, 'role');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, [
            'pattern' => '^[a-zA-Z]+$',
            'minLength' => 3,
            'maxLength' => 20,
            'enum' => ['admin', 'user', 'guest'],
        ]);
    }

    public function testNoConstraintsReturnsEmptyArray(): void
    {
        $testClass = new class {
            public string $plainField;
        };

        $property = new \ReflectionProperty($testClass, 'plainField');
        $propertyWrapper = new Property(
            property: $property,
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        $rules = $this->extractor->extractValidationRules($propertyWrapper, SchemaType::String);

        Assert::equals($rules, []);
    }

    public function testConstraintsSkipIncompatibleTypes(): void
    {
        $testClass = new class {
            #[Pattern('^\d+$')]
            #[Range(min: 1, max: 5)]
            #[Length(min: 2, max: 4)]
            public string $value;
        };

        $propertyWrapper = new Property(
            property: new \ReflectionProperty($testClass, 'value'),
            type: new Type([new SimpleType('string', true)]),
            hasDefaultValue: false,
        );

        Assert::same($this->extractor->extractValidationRules($propertyWrapper, SchemaType::Integer), ['minimum' => 1, 'maximum' => 5]);
        Assert::same($this->extractor->extractValidationRules($propertyWrapper, SchemaType::String), ['pattern' => '^\d+$', 'minLength' => 2, 'maxLength' => 4]);
        Assert::same($this->extractor->extractValidationRules($propertyWrapper, SchemaType::Boolean), []);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->extractor = new AttributeConstraintExtractor();
    }
}
