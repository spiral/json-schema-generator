<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Validation\DocBlockParser;

final class DocBlockParserTest extends TestCase
{
    private DocBlockParser $parser;

    public function testParsePositiveInt(): void
    {
        $docComment = '/** @var positive-int */';
        $constraints = $this->parser->parseDocComment($docComment);

        $this->assertContains('positive-int', $constraints);
    }

    public function testParseNonEmptyString(): void
    {
        $docComment = '/** @var non-empty-string */';
        $constraints = $this->parser->parseDocComment($docComment);

        $this->assertContains('non-empty-string', $constraints);
    }

    public function testParseIntRange(): void
    {
        $docComment = '/** @var int<0, 100> */';
        $constraints = $this->parser->parseDocComment($docComment);

        $this->assertContains(['int-range' => ['0', '100']], $constraints);
    }

    public function testParseIntRangeWithMin(): void
    {
        $docComment = '/** @var int<min, 0> */';
        $constraints = $this->parser->parseDocComment($docComment);

        $this->assertContains(['int-range' => ['min', '0']], $constraints);
    }

    public function testParseArrayShape(): void
    {
        $docComment = '/** @var array{theme: string, notifications: bool} */';
        $constraints = $this->parser->parseDocComment($docComment);

        $expected = ['array-shape' => ['theme' => 'string', 'notifications' => 'bool']];
        $this->assertContains($expected, $constraints);
    }

    public function testParseNumericString(): void
    {
        $docComment = '/** @var numeric-string */';
        $constraints = $this->parser->parseDocComment($docComment);

        $this->assertContains('numeric-string', $constraints);
    }

    public function testParseNonEmptyArray(): void
    {
        $docComment = '/** @var non-empty-array<string> */';
        $constraints = $this->parser->parseDocComment($docComment);

        $this->assertContains('non-empty-array', $constraints);
    }

    public function testParseMultipleConstraints(): void
    {
        $docComment = '/** @var positive-int */';
        $constraints = $this->parser->parseDocComment($docComment);

        $this->assertIsArray($constraints);
        $this->assertNotEmpty($constraints);
    }

    protected function setUp(): void
    {
        $this->parser = new DocBlockParser();
    }
}
