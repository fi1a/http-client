<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Handlers;

use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\CurlHandler;
use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;
use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\Request;
use Fi1a\HttpClient\Response;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;
use UnexpectedValueException;

/**
 * Curl-обработчик запросов
 */
class CurlHandlerTest extends ServerTestCase
{
    /**
     * Возвращает обработчик запросов
     */
    private function getHandler(): HandlerInterface
    {
        return new CurlHandler(new Config(['ssl_verify' => false]));
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
     * Ошибка при создании ресурса Curl
     */
    public function testCurlInitError(): void
    {
        $this->expectException(ConnectionErrorException::class);
        $handler = $this->getMockBuilder(CurlHandler::class)
            ->setConstructorArgs([new Config(['ssl_verify' => false])])
            ->onlyMethods(['curlInit'])
            ->getMock();

        $handler->method('curlInit')->willReturn(false);

        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar']);
        $handler->send($request, new Response());
    }

    /**
     * Исключении при остуствии подерржки HTTP 2.0
     */
    public function testExceptionOnHttp20(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $handler = $this->getMockBuilder(CurlHandler::class)
            ->setConstructorArgs([new Config(['ssl_verify' => false,])])
            ->onlyMethods(['isSupportHttp20'])
            ->getMock();
        $handler->method('isSupportHttp20')->willReturn(false);

        $request = Request::create()
            ->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar'])
            ->withProtocolVersion('2.0');

        $handler->send($request, new Response());
    }
}
