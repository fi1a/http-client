<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\ContentTypeParsers\ContentTypeParserInterface;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\ParserRegistry;
use Fi1a\Unit\HttpClient\Fixtures\XContentTypeParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Реестр парсеров типов контента
 */
class ParserRegistryTest extends TestCase
{
    /**
     * Добавить парсер
     */
    public function testAdd(): void
    {
        $this->assertFalse(ParserRegistry::has('x-content-type'));
        $this->assertFalse(ParserRegistry::has('X-CONTENT-TYPE'));
        ParserRegistry::add('x-content-type', XContentTypeParser::class);
        ParserRegistry::add('xhtml', XContentTypeParser::class);
        $this->assertTrue(ParserRegistry::has(MimeInterface::XHTML));
        $this->assertTrue(ParserRegistry::has('xhtml'));
        $this->assertTrue(ParserRegistry::has('x-content-type'));
        $this->assertTrue(ParserRegistry::has('X-CONTENT-TYPE'));
    }

    /**
     * Получить парсер
     */
    public function testGet(): void
    {
        $this->assertFalse(ParserRegistry::get('unknown'));
        $this->assertInstanceOf(ContentTypeParserInterface::class, ParserRegistry::get('x-content-type'));
        $this->assertInstanceOf(ContentTypeParserInterface::class, ParserRegistry::get('X-CONTENT-TYPE'));
    }

    /**
     * Исключение при пустом типе контента
     */
    public function testAddContentTypeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ParserRegistry::add('', XContentTypeParser::class);
    }

    /**
     * Исключение при классе парсера не реализующего интерфейс
     */
    public function testAddException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ParserRegistry::add('unknown', static::class);
    }

    /**
     * Исключение при наличии парсера для типа контента
     */
    public function testAddExistsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ParserRegistry::add('x-content-type', XContentTypeParser::class);
    }

    /**
     *  Удаление парсера для типа контента
     */
    public function testDelete(): void
    {
        $this->assertTrue(ParserRegistry::has('application/html+xml'));
        $this->assertTrue(ParserRegistry::has('xhtml'));
        $this->assertFalse(ParserRegistry::delete('unknwon'));
        $this->assertTrue(ParserRegistry::delete('x-content-type'));
        $this->assertTrue(ParserRegistry::delete('xhtml'));
        $this->assertFalse(ParserRegistry::has(MimeInterface::XHTML));
        $this->assertFalse(ParserRegistry::has('xhtml'));
        $this->assertFalse(ParserRegistry::delete('x-content-type'));
    }
}
