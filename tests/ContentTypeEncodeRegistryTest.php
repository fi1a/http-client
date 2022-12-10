<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\ContentTypeEncodeRegistry;
use Fi1a\HttpClient\ContentTypeEncodes\ContentTypeEncodeInterface;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\Unit\HttpClient\Fixtures\XContentTypeEncode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Реестр парсеров типов контента
 */
class ContentTypeEncodeRegistryTest extends TestCase
{
    /**
     * Добавить парсер
     */
    public function testAdd(): void
    {
        $this->assertFalse(ContentTypeEncodeRegistry::has('x-content-type'));
        $this->assertFalse(ContentTypeEncodeRegistry::has('X-CONTENT-TYPE'));
        ContentTypeEncodeRegistry::add('x-content-type', XContentTypeEncode::class);
        ContentTypeEncodeRegistry::add('xhtml', XContentTypeEncode::class);
        $this->assertTrue(ContentTypeEncodeRegistry::has(MimeInterface::XHTML));
        $this->assertTrue(ContentTypeEncodeRegistry::has('xhtml'));
        $this->assertTrue(ContentTypeEncodeRegistry::has('x-content-type'));
        $this->assertTrue(ContentTypeEncodeRegistry::has('X-CONTENT-TYPE'));
    }

    /**
     * Получить парсер
     */
    public function testGet(): void
    {
        $this->assertFalse(ContentTypeEncodeRegistry::get('unknown'));
        $this->assertInstanceOf(ContentTypeEncodeInterface::class, ContentTypeEncodeRegistry::get('x-content-type'));
        $this->assertInstanceOf(ContentTypeEncodeInterface::class, ContentTypeEncodeRegistry::get('X-CONTENT-TYPE'));
    }

    /**
     * Исключение при пустом типе контента
     */
    public function testAddContentTypeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ContentTypeEncodeRegistry::add('', XContentTypeEncode::class);
    }

    /**
     * Исключение при классе парсера не реализующего интерфейс
     */
    public function testAddException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ContentTypeEncodeRegistry::add('unknown', static::class);
    }

    /**
     * Исключение при наличии парсера для типа контента
     */
    public function testAddExistsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ContentTypeEncodeRegistry::add('x-content-type', XContentTypeEncode::class);
    }

    /**
     *  Удаление парсера для типа контента
     */
    public function testDelete(): void
    {
        $this->assertTrue(ContentTypeEncodeRegistry::has('application/html+xml'));
        $this->assertTrue(ContentTypeEncodeRegistry::has('xhtml'));
        $this->assertFalse(ContentTypeEncodeRegistry::delete('unknwon'));
        $this->assertTrue(ContentTypeEncodeRegistry::delete('x-content-type'));
        $this->assertTrue(ContentTypeEncodeRegistry::delete('xhtml'));
        $this->assertFalse(ContentTypeEncodeRegistry::has(MimeInterface::XHTML));
        $this->assertFalse(ContentTypeEncodeRegistry::has('xhtml'));
        $this->assertFalse(ContentTypeEncodeRegistry::delete('x-content-type'));
    }
}
