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
     * Отправка GET запроса
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
}
