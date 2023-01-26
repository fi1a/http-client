<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Handlers;

use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;
use Fi1a\HttpClient\Handlers\Exceptions\ErrorException;
use Fi1a\HttpClient\Handlers\Exceptions\TimeoutErrorException;
use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\Request;
use Fi1a\HttpClient\Response;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;

/**
 * Stream-обработчик запросов
 */
class StreamHandlerTest extends ServerTestCase
{
    /**
     * Возвращает обработчик запросов
     */
    private function getHandler(): HandlerInterface
    {
        return new StreamHandler(new Config(['sslVerify' => false]));
    }

    /**
     * Исключение при соединении
     */
    public function testConnectErrorException(): void
    {
        $this->expectException(ConnectionErrorException::class);
        $handler = $this->getHandler();
        $request = Request::create()->get('https://127.0.0.1:10/');
        $handler->send($request, new Response());
    }

    /**
     * POST запрос
     */
    public function testPostSend(): void
    {
        $handler = $this->getHandler();
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar']);
        $request->withHeader('Content-Type', $request->getBody()->getContentType());
        $request->withHeader('Content-Length', (string) $request->getBody()->getSize());
        $response = $handler->send($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * POST запрос
     */
    public function testGetSendNullContentLenght(): void
    {
        $handler = $this->getHandler();
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-null-content-length');
        $response = $handler->send($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
        $this->assertIsString($response->getBody()->get());
        $this->assertIsString($response->getBody()->getRaw());
        $this->assertEquals(1000000, mb_strlen($response->getBody()->get()));
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Ошибка при чтении заголовков
     */
    public function testReadHeaderException(): void
    {
        $this->expectException(ErrorException::class);
        $handler = $this->getMockBuilder(StreamHandler::class)
            ->setConstructorArgs([new Config(['sslVerify' => false])])
            ->onlyMethods(['readContentLine'])
            ->getMock();

        $handler->method('readContentLine')->willReturn(false);
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar']);
        $handler->send($request, new Response());
    }

    /**
     * Ошибка при чтении заголовков
     */
    public function testReadHeaderTimeoutException(): void
    {
        $this->expectException(TimeoutErrorException::class);
        $handler = $this->getMockBuilder(StreamHandler::class)
            ->setConstructorArgs([new Config(['sslVerify' => false,])])
            ->onlyMethods(['getMetaData'])
            ->getMock();

        $handler->method('getMetaData')->willReturn(['timed_out' => 20]);
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar']);
        $handler->send($request, new Response());
    }

    /**
     * Проверяем возвращенные данные и если ничего не вернули выбрасываем исключение
     */
    public function testIsConnectionErrorException(): void
    {
        $this->expectException(ConnectionErrorException::class);
        $handler = $this->getMockBuilder(StreamHandler::class)
            ->setConstructorArgs([new Config(['sslVerify' => false,])])
            ->onlyMethods(['isConnectionError'])
            ->getMock();

        $handler->method('isConnectionError')->willReturn(true);
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar']);
        $handler->send($request, new Response());
    }
}
