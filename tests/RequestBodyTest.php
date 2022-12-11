<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\HttpInterface;
use Fi1a\HttpClient\Mime;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\Request;
use Fi1a\HttpClient\RequestBody;
use Fi1a\HttpClient\RequestBodyInterface;
use PHPUnit\Framework\TestCase;

/**
 * Тело запроса
 */
class RequestBodyTest extends TestCase
{
    /**
     * Возвращает тело запроса
     */
    private function getRequestBody(): RequestBodyInterface
    {
        return new RequestBody(Request::create());
    }

    /**
     * Тестирование тела запроса
     */
    public function testBody(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = $this->getRequestBody();
        $body->withBody($array, 'json');
        $this->assertEquals(MimeInterface::JSON, $body->getContentType());
        $this->assertEquals($array, $body->getRaw());
        $this->assertEquals($json, $body->get());
    }

    /**
     * Парсинг контента при смене типа
     */
    public function testParseOnChangeContentType(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = $this->getRequestBody();
        $body->withBody($array);
        $this->assertNull($body->getContentType());
        $this->assertEquals($array, $body->getRaw());
        $body->withContentType('json');
        $this->assertEquals(MimeInterface::JSON, $body->getContentType());
        $this->assertEquals($array, $body->getRaw());
        $this->assertEquals($json, $body->get());
    }

    /**
     * Тестирование тела запроса при пустом типе контента
     */
    public function testWithoutContentType(): void
    {
        $content = 'content';
        $body = $this->getRequestBody();
        $body->withBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals($content, $body->get());
    }

    /**
     * Тестирование тела ответа при пустом теле запроса
     */
    public function testEmptyContent(): void
    {
        $content = '';
        $body = $this->getRequestBody();
        $body->withBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals($content, $body->get());
    }

    /**
     * Тестирование тела запроса при пустом типе контента
     */
    public function testWithoutContentTypeWithArray(): void
    {
        $content = ['foo' => 'bar'];
        $body = $this->getRequestBody();
        $body->withBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals('', $body->get());
    }

    /**
     * Наличие тела запроса
     */
    public function testHas(): void
    {
        $body = $this->getRequestBody();
        $body->withBody('content');
        $this->assertTrue($body->has());
    }

    /**
     * Наличие тела запроса
     */
    public function testHasEmptyString(): void
    {
        $body = $this->getRequestBody();
        $body->withBody('');
        $this->assertFalse($body->has());
    }

    /**
     * Размер запроса
     */
    public function testGetSize(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = $this->getRequestBody();
        $body->withBody($array, 'json');
        $this->assertEquals(mb_strlen($json), $body->getSize());
    }

    /**
     * Устанавливаемын заголовки размер запроса
     */
    public function testHeaderContentLength(): void
    {
        $request = Request::create()->withMethod(HttpInterface::POST);
        $body = new RequestBody($request);
        $body->withBody(['foo' => 'bar'], Mime::JSON);
        $this->assertTrue($request->hasHeader('Content-Length'));
        $header = $request->getLastHeader('Content-Length');
        $this->assertEquals((string) $body->getSize(), $header->getValue());
    }

    /**
     * Устанавливаемын заголовки по умолчанию
     */
    public function testHeaderContentTypePlainText(): void
    {
        $request = Request::create();
        $body = new RequestBody($request);
        $body->withBody('foo');
        $this->assertTrue($request->hasHeader('Content-Type'));
        $header = $request->getLastHeader('Content-Type');
        $this->assertEquals(MimeInterface::HTML, $header->getValue());
    }

    /**
     * Устанавливаемын заголовки при POST запросе
     */
    public function testHeaderContentTypeFormPost(): void
    {
        $request = Request::create()->withMethod(HttpInterface::POST);
        $body = new RequestBody($request);
        $body->withBody(['foo' => 'bar']);
        $this->assertTrue($request->hasHeader('Content-Type'));
        $header = $request->getLastHeader('Content-Type');
        $this->assertEquals(MimeInterface::FORM, $header->getValue());
    }

    /**
     * Устанавливаемын заголовки при PUT запросе
     */
    public function testHeaderContentTypeFormPut(): void
    {
        $request = Request::create()->withMethod(HttpInterface::PUT);
        $body = new RequestBody($request);
        $body->withBody(['foo' => 'bar']);
        $this->assertTrue($request->hasHeader('Content-Type'));
        $header = $request->getLastHeader('Content-Type');
        $this->assertEquals(MimeInterface::FORM, $header->getValue());
    }
}
