<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;
use Testo\Assert;
use Testo\Test;

#[Test]
final class FieldTest
{
    #[Field]
    private string $default = '';

    #[Field(title: 'Title', description: 'Description', default: 'foo', format: Format::Email)]
    private string $withValues = 'foo';

    public function testFieldWithDefaultValues(): void
    {
        $ref = new \ReflectionProperty(self::class, 'default');

        $attr = $ref->getAttributes(Field::class)[0]->newInstance();

        Assert::same($attr->title, '');
        Assert::same($attr->description, '');
        Assert::null($attr->default);
    }

    public function testFieldWithValues(): void
    {
        $ref = new \ReflectionProperty(self::class, 'withValues');

        $attr = $ref->getAttributes(Field::class)[0]->newInstance();

        Assert::same($attr->title, 'Title');
        Assert::same($attr->description, 'Description');
        Assert::same($attr->default, 'foo');
    }

    public function testFieldWithFormat(): void
    {
        $ref = new \ReflectionProperty(self::class, 'withValues');

        $attr = $ref->getAttributes(Field::class)[0]->newInstance();
        Assert::instanceOf($attr->format, Format::class);
        Assert::same($attr->format->value, 'email');
    }
}
