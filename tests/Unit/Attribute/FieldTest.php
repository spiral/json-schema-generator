<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

final class FieldTest extends TestCase
{
    #[Field]
    private string $default = '';

    #[Field(title: 'Title', description: 'Description', default: 'foo', format: Format::Email)]
    private string $withValues = 'foo';

    public function testFieldWithDefaultValues(): void
    {
        $ref = new \ReflectionProperty(self::class, 'default');

        $attr = $ref->getAttributes(Field::class)[0]->newInstance();

        $this->assertSame('', $attr->title);
        $this->assertSame('', $attr->description);
        $this->assertNull($attr->default);
    }

    public function testFieldWithValues(): void
    {
        $ref = new \ReflectionProperty(self::class, 'withValues');

        $attr = $ref->getAttributes(Field::class)[0]->newInstance();

        $this->assertSame('Title', $attr->title);
        $this->assertSame('Description', $attr->description);
        $this->assertSame('foo', $attr->default);
    }

    public function testFieldWithFormat(): void
    {
        $ref = new \ReflectionProperty(self::class, 'withValues');

        $attr = $ref->getAttributes(Field::class)[0]->newInstance();
        $this->assertInstanceOf('Spiral\JsonSchemaGenerator\Schema\Format', $attr->format);
        $this->assertSame('email', $attr->format->value);
    }
}
