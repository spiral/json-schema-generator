<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Exception\GeneratorException;
use Spiral\JsonSchemaGenerator\Parser\ClassParser;
use Spiral\JsonSchemaGenerator\Parser\Parser;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class ParserTest extends TestCase
{
    public function testParse(): void
    {
        $this->assertInstanceOf(ClassParser::class, (new Parser())->parse(Movie::class));
        $this->assertInstanceOf(ClassParser::class, (new Parser())->parse(new \ReflectionClass(Movie::class)));
    }

    public function testParseException(): void
    {
        $this->expectException(GeneratorException::class);
        $this->assertInstanceOf(ClassParser::class, (new Parser())->parse('foo'));
    }
}
