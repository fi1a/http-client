<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\Header;
use Fi1a\HttpClient\HeaderInterface;
use Fi1a\HttpClient\Message;
use Fi1a\HttpClient\MessageInterface;
use PHPUnit\Framework\TestCase;

/**
 * Сообщение
 */
class MessageTest extends TestCase
{
    private function getMessage(): MessageInterface
    {
        $message = new Message();
        $collection = $message->getHeaders();
        $collection[] = ['Content-Type', 'text/html; charset=utf-8'];
        $collection[] = ['Content-Type', ''];
        $collection[] = new Header('CONTENT-TYPE', 'text/html; charset=utf-8');

        return $message;
    }

    /**
     * HTTP версия протокола по умолчанию
     */
    public function testDefaultProtocol(): void
    {
        $message = $this->getMessage();
        $this->assertEquals('1.1', $message->getProtocolVersion());
    }

    /**
     * HTTP версия протокола по умолчанию
     */
    public function testProtocol(): void
    {
        $message = $this->getMessage();
        $message->withProtocolVersion('1.0');
        $this->assertEquals('1.0', $message->getProtocolVersion());
    }

    /**
     * Добавление заголовка
     */
    public function testWithHeader(): void
    {
        $message = new Message();
        $message->withHeader('Content-Type', 'text/html; charset=utf-8');
        $this->assertCount(1, $message->getHeaders());
        $message->withHeader('Content-Type', '');
        $this->assertCount(2, $message->getHeaders());
    }

    /**
     * Добавляет заголовок с определенным именем и значением и возвращает объект заголовка
     */
    public function testWithAddedHeader(): void
    {
        $message = new Message();
        $this->assertInstanceOf(
            HeaderInterface::class,
            $message->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
        );
    }

    /**
     * Проверяет наличие заголовка с определенным именем
     */
    public function testHasHeader(): void
    {
        $message = $this->getMessage();
        $this->assertTrue($message->hasHeader('Content-Type'));
        $this->assertTrue($message->hasHeader('CONTENT-TYPE'));
        $this->assertFalse($message->hasHeader('Content-Range'));
    }

    /**
     * Возвращает заголовок с определенным именем
     */
    public function testGetHeader(): void
    {
        $message = $this->getMessage();
        $this->assertCount(3, $message->getHeader('Content-Type'));
        $this->assertCount(3, $message->getHeader('CONTENT-TYPE'));
        $this->assertCount(0, $message->getHeader('Content-Range'));
    }

    /**
     * Возвращает первый найденный заголовок с определенным именем
     */
    public function testGetFirstHeader(): void
    {
        $message = $this->getMessage();
        $header = $message->getFirstHeader('Content-Type');
        $this->assertInstanceOf(HeaderInterface::class, $header);
        $this->assertEquals('Content-Type', $header->getName());
        $this->assertNull($message->getFirstHeader('Content-Range'));
    }

    /**
     * Возвращает первый найденный заголовок с определенным именем
     */
    public function testLastHeader(): void
    {
        $message = $this->getMessage();
        $header = $message->getLastHeader('Content-Type');
        $this->assertInstanceOf(HeaderInterface::class, $header);
        $this->assertEquals('CONTENT-TYPE', $header->getName());
        $this->assertNull($message->getLastHeader('Content-Range'));
    }

    /**
     * Удаляет заголовок с определенным именем
     */
    public function testWithoutHeader(): void
    {
        $message = $this->getMessage();
        $this->assertTrue($message->withoutHeader('Content-Type'));
        $this->assertCount(0, $message->getHeader('Content-Type'));
        $message = $this->getMessage();
        $this->assertTrue($message->withoutHeader('CONTENT-TYPE'));
        $this->assertFalse($message->withoutHeader('CONTENT-TYPE'));
        $this->assertCount(0, $message->getHeader('Content-Type'));
    }
}
