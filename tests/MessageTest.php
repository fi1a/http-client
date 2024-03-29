<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Http\Header;
use Fi1a\Http\HeaderCollection;
use Fi1a\Http\HeaderInterface;
use Fi1a\HttpClient\Cookie\Cookie;
use Fi1a\HttpClient\Cookie\CookieCollectionInterface;
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
        $message = $message->withProtocolVersion('1.0');
        $this->assertEquals('1.0', $message->getProtocolVersion());
    }

    /**
     * Добавление заголовка
     */
    public function testWithHeader(): void
    {
        $message = new Message();
        $message = $message->withHeader('Content-Type', 'text/html; charset=utf-8');
        $this->assertCount(1, $message->getHeaders());
        $message = $message->withHeader('Content-Type', '');
        $this->assertCount(2, $message->getHeaders());
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
        $message = $message->withoutHeader('Content-Type');
        $this->assertFalse($message->hasHeader('Content-Type'));
        $this->assertCount(0, $message->getHeader('Content-Type'));
        $message = $this->getMessage();
        $message = $message->withoutHeader('CONTENT-TYPE');
        $this->assertFalse($message->hasHeader('Content-Type'));
        $message = $message->withoutHeader('CONTENT-TYPE');
        $this->assertFalse($message->hasHeader('Content-Type'));
        $this->assertCount(0, $message->getHeader('Content-Type'));
    }

    /**
     * Возвращает кодировку по умолчанию
     */
    public function testDefaultEncoding(): void
    {
        $message = $this->getMessage();
        $this->assertEquals('utf-8', $message->getEncoding());
    }

    /**
     * Возвращает кодировку по умолчанию
     */
    public function testEncoding(): void
    {
        $message = $this->getMessage();
        $message = $message->withEncoding('windows-1251');
        $this->assertEquals('windows-1251', $message->getEncoding());
    }

    /**
     * Удаляет все заголовки
     */
    public function testClearHeaders(): void
    {
        $message = $this->getMessage();
        $this->assertCount(3, $message->getHeaders());
        $message = $message->clearHeaders();
        $this->assertCount(0, $message->getHeaders());
    }

    /**
     * Установить коллекцию заголовков
     */
    public function testWithHeaders(): void
    {
        $message = new Message();
        $this->assertCount(0, $message->getHeaders());
        $headers = new HeaderCollection();
        $headers->add(new Header('Test-Header', 'Value1'));
        $message = $message->withHeaders($headers);
        $this->assertCount(1, $message->getHeaders());
    }

    /**
     * Добавить заголовок
     */
    public function testAddHeader(): void
    {
        $message = new Message();
        $this->assertCount(0, $message->getHeaders());
        $message->addHeader(new Header('Test-Header', 'Value1'));
        $this->assertCount(1, $message->getHeaders());
    }

    /**
     * Куки
     */
    public function testCookies(): void
    {
        $message = new Message();
        $this->assertInstanceOf(CookieCollectionInterface::class, $message->getCookies());
        $this->assertCount(0, $message->getCookies());
        $cookies = $message->getCookies();
        $cookies[] = new Cookie(['Name' => 'CookieName1', 'Value' => 'Value1']);
        $message->withCookies($cookies);
        $this->assertCount(1, $message->getCookies());
    }
}
