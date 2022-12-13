<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\Request;
use Fi1a\HttpClient\Uri;
use Fi1a\Unit\HttpClient\Fixtures\RequestMiddlewares\ResponseSet500StatusMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\RequestMiddlewares\ResponseStopMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\RequestMiddlewares\Set500StatusMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\RequestMiddlewares\StopMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\RequestMiddlewares\UnknownContentEncodingMiddleware;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;
use InvalidArgumentException;

/**
 * HTTP-client
 */
class HttpClientTest extends ServerTestCase
{
    /**
     * Возвращает HTTP-client
     */
    private function getStreamClient(): HttpClientInterface
    {
        return new HttpClient(new Config(['ssl_verify' => false]), StreamHandler::class);
    }

    /**
     * Настроенные клиенты для тестов
     *
     * @return HttpClientInterface[][]
     */
    public function clientDataProvider(): array
    {
        return [
            [
                $this->getStreamClient(),
            ],
        ];
    }

    /**
     * Отправка запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testSendEmptyHostException(HttpClientInterface $client): void
    {
        $this->expectException(InvalidArgumentException::class);
        $request = Request::create()->get('https');
        $client->send($request);
    }

    /**
     * Отправка GET запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testGetSendTextPlainResponse(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::PLAIN, $response->getBody()->getContentType());
        $this->assertEquals('success', $response->getBody()->get());
        $this->assertEquals('success', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка GET запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testGetSendJsonResponse(HttpClientInterface $client): void
    {
        $uri = new Uri('https://' . self::HOST . '/200-ok-json');
        $uri->withQueryParams(['foo' => 'bar']);
        $request = Request::create()->get($uri);
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка POST запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testPostSend(HttpClientInterface $client): void
    {
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar']);
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка GET запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testGet(HttpClientInterface $client): void
    {
        $uri = new Uri('https://' . self::HOST . '/200-ok-json');
        $uri->withQueryParams(['foo' => 'bar']);
        $response = $client->get($uri, 'json');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка POST запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testPost(HttpClientInterface $client): void
    {
        $response = $client->post(
            'https://' . self::HOST . '/200-ok-post',
            ['foo' => 'bar'],
            'form'
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка PUT запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testPut(HttpClientInterface $client): void
    {
        $response = $client->put(
            'https://' . self::HOST . '/200-ok-put',
            ['foo' => 'bar'],
            'form'
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка PATCH запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testPatch(HttpClientInterface $client): void
    {
        $response = $client->patch(
            'https://' . self::HOST . '/200-ok-patch',
            ['foo' => 'bar'],
            'form'
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка DELETE запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testDelete(HttpClientInterface $client): void
    {
        $uri = new Uri('https://' . self::HOST . '/200-ok-delete');
        $uri->withQueryParams(['foo' => 'bar']);
        $response = $client->delete($uri);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Отправка HEAD запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testHead(HttpClientInterface $client): void
    {
        $response = $client->head('https://' . self::HOST . '/200-ok-head');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertFalse($response->getBody()->has());
    }

    /**
     * Отправка OPTIONS запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testOptions(HttpClientInterface $client): void
    {
        $response = $client->options('https://' . self::HOST . '/200-ok-options');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::PLAIN, $response->getBody()->getContentType());
        $this->assertEquals('success', $response->getBody()->get());
        $this->assertEquals('success', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * 404 статус ответа
     *
     * @dataProvider clientDataProvider
     */
    public function test404Status(HttpClientInterface $client): void
    {
        $uri = new Uri('https://' . self::HOST . '/404-not-found');
        $uri->withQueryParams(['foo' => 'bar']);
        $response = $client->get($uri);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Промежуточное ПО для запроса (останаливает запрос)
     *
     * @dataProvider clientDataProvider
     */
    public function testStopMiddleware(HttpClientInterface $client): void
    {
        $client->addMiddleware(new StopMiddleware());
        $response = $client->get('https://' . self::HOST . '/200-ok-text-plain');
        $this->assertEquals(0, $response->getStatusCode());
        $this->assertEquals('', $response->getReasonPhrase());
        $this->assertFalse($response->getBody()->has());
    }

    /**
     * Промежуточное ПО для запроса (сортировка)
     *
     * @dataProvider clientDataProvider
     */
    public function testSortRequestMiddleware(HttpClientInterface $client): void
    {
        $client->addMiddleware(new StopMiddleware(), 600);
        $client->addMiddleware(new Set500StatusMiddleware(), 100);
        $response = $client->get('https://' . self::HOST . '/200-ok-text-plain');
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Промежуточное ПО для ответа (сортировка)
     *
     * @dataProvider clientDataProvider
     */
    public function testSortResponseMiddleware(HttpClientInterface $client): void
    {
        $client->addMiddleware(new ResponseStopMiddleware(), 600);
        $client->addMiddleware(new ResponseSet500StatusMiddleware(), 100);
        $response = $client->get('https://' . self::HOST . '/200-ok-text-plain');
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Конструктор
     */
    public function testConstructor(): void
    {
        $client = new HttpClient(new Config(['ssl_verify' => false]), StreamHandler::class);
        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    /**
     * Конструктор
     */
    public function testConstructorHandlerException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HttpClient(new Config(['ssl_verify' => false]), static::class);
    }

    /**
     * Выставляем заголовок Accept
     */
    public function testContentHeadersAccept(): void
    {
        $client = $this->getStreamClient();
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain', 'json');
        $response = $client->send($request);
        $this->assertEquals(MimeInterface::JSON, $request->getLastHeader('Accept')->getValue());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::PLAIN, $response->getBody()->getContentType());
        $this->assertEquals('success', $response->getBody()->get());
        $this->assertEquals('success', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Выставляем заголовок Content-Type
     */
    public function testContentHeadersContentTypeForm(): void
    {
        $client = $this->getStreamClient();
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post', ['foo' => 'bar']);
        $response = $client->send($request);
        $this->assertEquals(MimeInterface::FORM, $request->getLastHeader('Content-Type')->getValue());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals(['foo' => 'bar'], $response->getBody()->get());
        $this->assertEquals('{"foo":"bar"}', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Сжатие ответа
     */
    public function testGetGzipContentEncoding(): void
    {
        $client = new HttpClient(
            new Config(['ssl_verify' => false, 'compress' => 'gzip']),
            StreamHandler::class
        );
        $request = Request::create()->get('https://' . self::HOST . '/index.html', 'html');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
        $this->assertEquals('utf-8', $response->getEncoding());
        $this->assertEquals('gzip', $response->getLastHeader('Content-Encoding')->getValue());
        $this->assertEquals('success', $response->getBody()->getRaw());
        $this->assertEquals('success', $response->getBody()->get());
    }

    /**
     * Неизвестное сжатие ответа
     */
    public function testUnknownContentEncoding(): void
    {
        $client = new HttpClient(
            new Config(['ssl_verify' => false,]),
            StreamHandler::class
        );
        $client->addMiddleware(new UnknownContentEncodingMiddleware());
        $request = Request::create()->get('https://' . self::HOST . '/index.html', 'html');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
        $this->assertEquals('utf-8', $response->getEncoding());
        $this->assertEquals('unknown', $response->getLastHeader('Content-Encoding')->getValue());
        $this->assertEquals('success', $response->getBody()->getRaw());
        $this->assertEquals('success', $response->getBody()->get());
    }

    /**
     * Тип контента по умолчанию
     */
    public function testDefaultContentType(): void
    {
        $client = $this->getStreamClient();
        $request = Request::create()->head('https://' . self::HOST . '/200-ok-head')->withBody('plain-text');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertFalse($response->getBody()->has());
    }
}
