<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\Filesystem;
use Fi1a\Http\MimeInterface;
use Fi1a\Http\Uri;
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Cookie\CookieInterface;
use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;
use Fi1a\HttpClient\Handlers\Exceptions\ErrorException;
use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\Handlers\HttpStreamProxyConnector;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Proxy\HttpProxy;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\Proxy\Socks5Proxy;
use Fi1a\HttpClient\Request;
use Fi1a\HttpClient\Response;
use Fi1a\HttpClient\UploadFileCollection;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\ResponseSet500StatusMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\ResponseStopMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\Set500StatusMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\StopMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\UnknownContentEncodingMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Proxy\FixtureProxy;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * HTTP-client
 */
class HttpClientTest extends ServerTestCase
{
    /**
     * Значения по умолчанию в конструктор
     */
    public function testEmptyConstructorArgs(): void
    {
        $client = new HttpClient();
        $request = Request::create()->get('http://' . self::HTTP_HOST . '/200-ok-text-plain/');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
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
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/');
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
     * Отправка GET запроса с проверкой кодирования адреса
     *
     * @dataProvider clientDataProvider
     */
    public function testGetEncoded(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://' . self::HOST . '/путь/для теста.html');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals('для теста', $response->getBody()->getRaw());
    }

    /**
     * Отправка GET запроса с проверкой punycode преобразования домена
     *
     * @dataProvider clientDataProvider
     */
    public function testGetPunycode(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://уфа.рф');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
    }

    /**
     * Отправка GET запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testGetSendJsonResponse(HttpClientInterface $client): void
    {
        $uri = new Uri('https://' . self::HOST . '/200-ok-json/');
        $uri = $uri->withQueryParams(['foo' => 'bar']);
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
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post/', ['foo' => 'bar']);
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
        $uri = new Uri('https://' . self::HOST . '/200-ok-json/');
        $uri = $uri->withQueryParams(['foo' => 'bar']);
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
            'https://' . self::HOST . '/200-ok-post/',
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
     * Отправка пустого POST запроса
     *
     * @dataProvider clientDataProvider
     */
    public function testPostEmpty(HttpClientInterface $client): void
    {
        $response = $client->post(
            'https://' . self::HOST . '/200-ok-post/',
            [],
            'form'
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals([], $response->getBody()->get());
        $this->assertEquals('{}', $response->getBody()->getRaw());
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
            'https://' . self::HOST . '/200-ok-put/',
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
            'https://' . self::HOST . '/200-ok-patch/',
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
        $uri = new Uri('https://' . self::HOST . '/200-ok-delete/');
        $uri = $uri->withQueryParams(['foo' => 'bar']);
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
        $response = $client->head('https://' . self::HOST . '/200-ok-head/');
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
        $response = $client->options('https://' . self::HOST . '/200-ok-options/');
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
        $uri = new Uri('https://' . self::HOST . '/404-not-found/');
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
        $client->withMiddleware(new StopMiddleware());
        $response = $client->get('https://' . self::HOST . '/200-ok-text-plain/');
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
        $client->withMiddleware(new StopMiddleware(), 600);
        $client->withMiddleware(new Set500StatusMiddleware(), 100);
        $response = $client->get('https://' . self::HOST . '/200-ok-text-plain/');
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Промежуточное ПО задается в запросе
     *
     * @dataProvider clientDataProvider
     */
    public function testSetMiddlewareInRequest(HttpClientInterface $client): void
    {
        $client->withMiddleware(new StopMiddleware(), 600);
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/')
            ->withMiddleware(new Set500StatusMiddleware(), 100);
        $response = $client->send($request);
        $this->assertEquals(500, $response->getStatusCode());

        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/');
        $response = $client->send($request);
        $this->assertEquals(0, $response->getStatusCode());
    }

    /**
     * Промежуточное ПО для ответа (сортировка)
     *
     * @dataProvider clientDataProvider
     */
    public function testSortResponseMiddleware(HttpClientInterface $client): void
    {
        $client->withMiddleware(new ResponseStopMiddleware(), 100);
        $client->withMiddleware(new ResponseSet500StatusMiddleware(), 600);
        $response = $client->get('https://' . self::HOST . '/200-ok-text-plain/');
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Конструктор
     */
    public function testConstructor(): void
    {
        $client = new HttpClient(new Config(['sslVerify' => false]), StreamHandler::class);
        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    /**
     * Конструктор
     */
    public function testConstructorHandlerException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HttpClient(new Config(['sslVerify' => false]), static::class);
    }

    /**
     * Выставляем заголовок Accept
     *
     * @dataProvider clientDataProvider
     */
    public function testContentHeadersAccept(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/', 'json')
            ->withExpectedType('json');

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
     *
     * @dataProvider clientDataProvider
     */
    public function testContentHeadersContentTypeForm(HttpClientInterface $client): void
    {
        $request = Request::create()->post('https://' . self::HOST . '/200-ok-post/', ['foo' => 'bar']);
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
     *
     * @dataProvider clientDataProvider
     */
    public function testGetGzipContentEncoding(HttpClientInterface $client): void
    {
        $client->getConfig()->setCompress('gzip');
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
     *
     * @dataProvider clientDataProvider
     */
    public function testUnknownContentEncoding(HttpClientInterface $client): void
    {
        $client->withMiddleware(new UnknownContentEncodingMiddleware());
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
     *
     * @dataProvider clientDataProvider
     */
    public function testDefaultContentType(HttpClientInterface $client): void
    {
        $request = Request::create()->head('https://' . self::HOST . '/200-ok-head/')->withBody('plain-text');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertFalse($response->getBody()->has());
    }

    /**
     * Версия HTTP 1.0
     *
     * @dataProvider clientDataProvider
     */
    public function testProtocolVersion10(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/')->withProtocolVersion('1.0');
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
     * Версия HTTP 1.1
     *
     * @dataProvider clientDataProvider
     */
    public function testProtocolVersion11(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/')->withProtocolVersion('1.1');
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
     * Версия HTTP 2.0
     *
     * @dataProvider clientDataProvider
     */
    public function testProtocolVersion20(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/')->withProtocolVersion('2.0');
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
     * Версия HTTP UNKNOWN
     *
     * @dataProvider clientDataProvider
     */
    public function testProtocolVersionUnknown(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://' . self::HOST . '/200-ok-text-plain/')
            ->withProtocolVersion('UNKNOWN');
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
     * Редиректы
     *
     * @dataProvider clientDataProvider
     */
    public function testRedirects(HttpClientInterface $client): void
    {
        $response = $client->get('https://' . self::HOST . '/redirect/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::PLAIN, $response->getBody()->getContentType());
        $this->assertEquals('success', $response->getBody()->get());
        $this->assertEquals('success', $response->getBody()->getRaw());
        $this->assertEquals('utf-8', $response->getEncoding());
    }

    /**
     * Редиректы не разрешены
     *
     * @dataProvider clientDataProvider
     */
    public function testNotAllowRedirects(HttpClientInterface $client): void
    {
        $client->getConfig()->setAllowRedirects(false);
        $response = $client->get('https://' . self::HOST . '/redirect/');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('Found', $response->getReasonPhrase());
    }

    /**
     * Редиректы не разрешены
     *
     * @dataProvider clientDataProvider
     */
    public function testMaxRedirectsException(HttpClientInterface $client): void
    {
        $client->getConfig()->setMaxRedirects(2);
        $this->expectException(ErrorException::class);
        $client->get('https://' . self::HOST . '/redirect-loop/');
    }

    /**
     * Куки
     *
     * @dataProvider clientDataProvider
     */
    public function testCookie(HttpClientInterface $client): void
    {
        $client->getConfig()->setCookie(true);
        $response = $client->get('https://' . self::HOST . '/cookie/');
        $this->assertCount(2, $response->getCookies());
        $this->assertInstanceOf(
            CookieInterface::class,
            $response->getCookies()->getByName('cookieName1')
        );
        $this->assertEquals(1, (int) $response->getCookies()->getByName('cookieName1')->getValue());
        $this->assertEquals('value2=value2', $response->getCookies()->getByName('cookieName2')->getValue());

        $request = Request::create()->get('https://' . self::HOST . '/send-cookie/');
        $response = $client->send($request);
        $this->assertCount(2, $request->getCookies());
        $this->assertInstanceOf(
            CookieInterface::class,
            $request->getCookies()->getByName('cookieName1')
        );
        $this->assertEquals(1, (int) $request->getCookies()->getByName('cookieName1')->getValue());
        $this->assertEquals('value2=value2', $request->getCookies()->getByName('cookieName2')->getValue());
        $this->assertCount(3, $response->getCookies());
        $this->assertInstanceOf(
            CookieInterface::class,
            $response->getCookies()->getByName('cookieName1')
        );
        $this->assertEquals(1, (int) $response->getCookies()->getByName('cookieName1')->getValue());
        $this->assertEquals('value2=value2', $response->getCookies()->getByName('cookieName2')->getValue());
        $this->assertEquals(1, (int) $response->getCookies()->getByName('cookieName3')->getValue());
    }

    /**
     * Куки (пропускает пустой заголовок)
     *
     * @dataProvider clientDataProvider
     */
    public function testCookieEmptyHeader(HttpClientInterface $client): void
    {
        $client->getConfig()->setCookie(true);
        $response = $client->get('https://' . self::HOST . '/cookie-empty/');
        $this->assertCount(0, $response->getCookies());
    }

    /**
     * Куки (устанавливает путь)
     *
     * @dataProvider clientDataProvider
     */
    public function testCookieSetPath(HttpClientInterface $client): void
    {
        $client->getConfig()->setCookie(true);
        $response = $client->get('https://' . self::HOST . '/cookie-path/');
        $this->assertCount(1, $response->getCookies());
        $this->assertEquals('/cookie-path', $response->getCookies()->getByName('cookieName3')->getPath());
    }

    /**
     * Куки (устанавливает путь)
     *
     * @dataProvider clientDataProvider
     */
    public function testCookieSetPathSlash(HttpClientInterface $client): void
    {
        $client->getConfig()->setCookie(true);
        $response = $client->get('https://' . self::HOST . '/cookie-path');
        $this->assertCount(1, $response->getCookies());
        $this->assertEquals('/', $response->getCookies()->getByName('cookieName3')->getPath());
    }

    /**
     * Префикс к адресам
     *
     * @dataProvider clientDataProvider
     */
    public function testWithUrlPrefix(HttpClientInterface $client): void
    {
        $client->withUrlPrefix('https://user:pass@' . self::HOST . '/200-ok-text-plain/');
        $response = $client->get('');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $client->withUrlPrefix(null);
        $response = $client->get('https://' . self::HOST . '/200-ok-text-plain/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $client->withUrlPrefix('https://' . self::HOST . '/');
        $response = $client->get('/200-ok-text-plain/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
    }

    /**
     * Ошибка соединения при протоколе http
     *
     * @dataProvider clientDataProvider
     */
    public function testConnectionError(HttpClientInterface $client): void
    {
        $this->expectException(ConnectionErrorException::class);
        $client->get('http://' . self::HOST . '/200-ok-text-plain/');
    }

    /**
     * Загрузка файлов
     *
     * @dataProvider clientDataProvider
     */
    public function testUploadFiles(HttpClientInterface $client): void
    {
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/Resources'));
        $files = new UploadFileCollection();
        $files[] = [
            'name' => 'file1',
            'file' => $filesystem->factoryFile('./file1.txt'),
        ];
        $files[] = [
            'name' => 'file2',
            'file' => $filesystem->factoryFile('./file2.txt'),
        ];

        $response = $client->post(
            'https://' . self::HOST . '/file-upload/',
            [
                'foo' => 'bar',
            ],
            MimeInterface::FORM,
            $files
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals('bar_file1_file2', $response->getBody()->get());
    }

    /**
     * Провайдер данных для тестирования прокси с различными хэндлерами
     *
     * @return mixed[][]
     */
    public function clientAndProxyDataProvider(): array
    {
        return [
            [
                $this->getStreamClient(),
                $this->getHttpProxy(),
            ],
            [
                $this->getCurlClient(),
                $this->getHttpProxy(),
            ],
            [
                $this->getStreamClient(),
                $this->getSocks5Proxy(),
            ],
            [
                $this->getCurlClient(),
                $this->getSocks5Proxy(),
            ],
        ];
    }

    /**
     * Отправка GET запроса с Https портом по умолчанию
     *
     * @dataProvider clientDataProvider
     */
    public function testGetDefaultHttpsPort(HttpClientInterface $client): void
    {
        $request = Request::create()->get('https://httpbin.org');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
    }

    /**
     * Отправка GET запроса с Http портом по умолчанию
     *
     * @dataProvider clientDataProvider
     */
    public function testGetDefaultHttpPort(HttpClientInterface $client): void
    {
        $request = Request::create()->get('http://httpbin.org');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
    }

    /**
     * Использовать прокси с различными хэндлерами по HTTP протоколу
     *
     * @dataProvider clientAndProxyDataProvider
     */
    public function testProxyHttp(HttpClientInterface $client, ProxyInterface $proxy): void
    {
        $client->withProxy($proxy);
        $request = Request::create()->get('http://' . self::HTTP_HOST);
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
    }

    /**
     * Использовать прокси с различными хэндлерами по HTTPS протоколу
     *
     * @dataProvider clientAndProxyDataProvider
     */
    public function testProxyHttps(HttpClientInterface $client, ProxyInterface $proxy): void
    {
        $client->withProxy($proxy);
        $request = Request::create()->get('https://' . self::HOST);
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->getBody()->has());
        $this->assertEquals(MimeInterface::HTML, $response->getBody()->getContentType());
    }

    /**
     * Использовать прокси для доступа к хосту с IP
     *
     * @dataProvider clientAndProxyDataProvider
     */
    public function testProxyRequestIp(HttpClientInterface $client, ProxyInterface $proxy): void
    {
        $client->withProxy($proxy);
        $request = Request::create()->get('http://' . self::HTTP_HOST . '/200-ok-text-plain/');
        $response = $client->send($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->getBody()->has());
    }

    /**
     * Исключение при неизвестном классе proxy
     *
     * @dataProvider clientDataProvider
     */
    public function testProxyFactoryException(HttpClientInterface $client): void
    {
        $this->expectException(LogicException::class);
        $client->withProxy(new FixtureProxy('127.0.0.1', 80));
        $request = Request::create()->get('http://httpbin.org');
        $client->send($request);
    }

    /**
     * Исключение при ошибке соединения с proxy socks5
     *
     * @dataProvider clientDataProvider
     */
    public function testSocks5ProxyConnectionFail(HttpClientInterface $client): void
    {
        $this->expectException(ConnectionErrorException::class);
        $client->withProxy(new Socks5Proxy('127.0.0.1', 10000));
        $request = Request::create()->get('http://httpbin.org');
        $client->send($request);
    }

    /**
     * Исключение при ошибке соединения с proxy http
     *
     * @dataProvider clientDataProvider
     */
    public function testHttpProxyConnectionFail(HttpClientInterface $client): void
    {
        $this->expectException(ConnectionErrorException::class);
        $client->withProxy(new HttpProxy('127.0.0.1', 10000));
        $request = Request::create()->get('http://httpbin.org');
        $client->send($request);
    }

    /**
     * Исключение при ошибке соединения с proxy socks5
     *
     * @dataProvider clientAndProxyDataProvider
     */
    public function testProxyAuthError(HttpClientInterface $client, ProxyInterface $proxy): void
    {
        $this->expectException(ConnectionErrorException::class);
        $proxy->setUserName('unknown');
        $client->withProxy($proxy);
        $request = Request::create()->get('http://' . self::HTTP_HOST);
        $client->send($request);
    }

    /**
     * Исключение при ошибке чтения потока
     */
    public function testHttpStreamProxyConnectorReadHeaderLineException(): void
    {
        $this->expectException(ConnectionErrorException::class);
        $proxy = $this->getHttpProxy();
        $config = new Config(['sslVerify' => false]);
        $request = Request::create()->get('http://' . self::HTTP_HOST . '/200-ok-text-plain/');

        $options = [];
        $options['ssl']['peer_name'] = $request->getUri()->host();
        $options['ssl']['verify_peer_name'] = false;
        $options['ssl']['verify_peer'] = false;
        $options['ssl']['allow_self_signed'] = true;
        $context = stream_context_create($options);

        $connector = $this->getMockBuilder(HttpStreamProxyConnector::class)
            ->onlyMethods(['readContentLine'])
            ->setConstructorArgs([$context, $config, $request, new Response(), $proxy])
            ->getMock();

        $connector->method('readContentLine')->willReturn(false);

        /**
         * @var HandlerInterface|MockObject $handler
         */
        $handler = $this->getMockBuilder(StreamHandler::class)
            ->onlyMethods(['factoryProxy'])
            ->setConstructorArgs([$config])
            ->getMock();

        $handler->method('factoryProxy')->willReturn($connector);

        $client = $this->getMockBuilder(HttpClient::class)
            ->onlyMethods(['factoryHandler'])
            ->setConstructorArgs([$config, StreamHandler::class])
            ->getMock();

        $client->method('factoryHandler')->willReturn($handler);

        $client->withProxy($proxy);
        $client->send($request);
    }

    /**
     * Исключение при ошибке соединения с proxy http
     *
     * @dataProvider clientDataProvider
     */
    public function testHttpsSslVerify(HttpClientInterface $client): void
    {
        $client->getConfig()->setSslVerify(true);
        $response = $client->get('https://httpbin.org/get');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
