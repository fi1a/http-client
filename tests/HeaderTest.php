<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\Header;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Заголовок
 */
class HeaderTest extends TestCase
{
    /**
     * Заголовок
     */
    public function testHeader(): void
    {
        $header = new Header('content-Type', 'text/html; charset=utf-8');
        $this->assertEquals('content-Type', $header->getName());
        $this->assertEquals('text/html; charset=utf-8', $header->getValue());
    }

    /**
     * Сохранение регистра названия
     */
    public function testHeaderCaseName(): void
    {
        $header = new Header('Content-Type', 'text/html; charset=utf-8');
        $this->assertEquals('Content-Type', $header->getName());
        $this->assertEquals('text/html; charset=utf-8', $header->getValue());
    }

    /**
     * Исключение при пустом названии
     */
    public function testHeaderNameException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Header('', 'text/html; charset=utf-8');
    }

    /**
     * Пустое значение
     */
    public function testHeaderEmptyValue(): void
    {
        $header = new Header('Content-Type', '');
        $this->assertEquals('Content-Type', $header->getName());
        $this->assertEquals('', $header->getValue());
    }

    /**
     * Null значение
     */
    public function testHeaderNullValue(): void
    {
        $header = new Header('Content-Type');
        $this->assertEquals('Content-Type', $header->getName());
        $this->assertNull($header->getValue());
    }

    /**
     * Возвращает строку заголовка
     */
    public function testHeaderLine(): void
    {
        $header = new Header('Content-Type', 'text/html; charset=utf-8');
        $this->assertEquals('Content-Type: text/html; charset=utf-8', $header->getLine());
    }

    /**
     * Преобразование объекта заголовка в строку
     */
    public function testToString(): void
    {
        $header = new Header('Content-Type', 'text/html; charset=utf-8');
        $this->assertEquals($header->getLine(), (string) $header);
    }
}
