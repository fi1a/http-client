<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Http\HttpInterface;
use Fi1a\Http\MimeInterface;
use Fi1a\Http\Uri;
use Fi1a\Http\UriInterface;
use Fi1a\HttpClient\Cookie\CookieInterface;
use Fi1a\HttpClient\Middlewares\MiddlewareCollectionInterface;
use Fi1a\HttpClient\Proxy\HttpProxy;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\Request;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\Set500StatusMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\StopMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * Объект запроса
 */
class RequestTest extends TestCase
{
    /**
     * Создать объект запроса
     */
    public function testCreate(): void
    {
        $this->assertInstanceOf(RequestInterface::class, Request::create());
    }

    /**
     * Метод запроса по умолчанию
     */
    public function testDefaultMethod(): void
    {
        $request = Request::create();
        $this->assertEquals(HttpInterface::GET, $request->getMethod());
    }

    /**
     * Устанавливаем метод запроса
     */
    public function testWithMethod(): void
    {
        $request = Request::create();
        $request->withMethod(HttpInterface::POST);
        $this->assertEquals(HttpInterface::POST, $request->getMethod());
    }

    /**
     * Устанавливаем метод запроса
     */
    public function testWithMethodCustom(): void
    {
        $request = Request::create();
        $request->withMethod('custom');
        $this->assertEquals('CUSTOM', $request->getMethod());
    }

    /**
     * GET запрос
     */
    public function testGet(): void
    {
        $request = Request::create();
        $request->get(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            'json'
        )->withExpectedType('json');
        $this->assertEquals(HttpInterface::GET, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertEquals(MimeInterface::JSON, $request->getBody()->getContentType());
        $this->assertEquals(MimeInterface::JSON, $request->getExpectedType());
    }

    /**
     * POST запрос
     */
    public function testPostDefaultContentType(): void
    {
        $post = ['foo' => 'bar'];
        $request = Request::create();
        $request->post(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $post
        );
        $this->assertEquals(HttpInterface::POST, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertEquals(MimeInterface::FORM, $request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
        $this->assertEquals($post, $request->getBody()->getRaw());
        $this->assertEquals(http_build_query($post, '', '&'), $request->getBody()->get());
    }

    /**
     * POST запрос
     */
    public function testPost(): void
    {
        $request = Request::create();
        $request->post(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            ['foo' => 'bar'],
            'json'
        )->withExpectedType('json');
        $this->assertEquals(HttpInterface::POST, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertEquals(MimeInterface::JSON, $request->getBody()->getContentType());
        $this->assertEquals(MimeInterface::JSON, $request->getExpectedType());
        $this->assertEquals(['foo' => 'bar'], $request->getBody()->getRaw());
    }

    /**
     * PUT запрос
     */
    public function testPutDefaultContentType(): void
    {
        $put = ['foo' => 'bar'];
        $request = Request::create();
        $request->put(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $put
        );
        $this->assertEquals(HttpInterface::PUT, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertEquals(MimeInterface::FORM, $request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
        $this->assertEquals($put, $request->getBody()->getRaw());
        $this->assertEquals(http_build_query($put, '', '&'), $request->getBody()->get());
    }

    /**
     * PUT запрос
     */
    public function testPut(): void
    {
        $request = Request::create();
        $request->put(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            ['foo' => 'bar'],
            'json'
        )->withExpectedType('json');
        $this->assertEquals(HttpInterface::PUT, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertEquals(MimeInterface::JSON, $request->getBody()->getContentType());
        $this->assertEquals(MimeInterface::JSON, $request->getExpectedType());
        $this->assertEquals(['foo' => 'bar'], $request->getBody()->getRaw());
    }

    /**
     * PATCH запрос
     */
    public function testPatch(): void
    {
        $request = Request::create();
        $request->patch(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            ['foo' => 'bar'],
            'json'
        )->withExpectedType('json');
        $this->assertEquals(HttpInterface::PATCH, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertEquals(MimeInterface::JSON, $request->getBody()->getContentType());
        $this->assertEquals(MimeInterface::JSON, $request->getExpectedType());
        $this->assertEquals(['foo' => 'bar'], $request->getBody()->getRaw());
    }

    /**
     * DELETE запрос
     */
    public function testDelete(): void
    {
        $request = Request::create();
        $request->delete(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            'json'
        )->withExpectedType('json');
        $this->assertEquals(HttpInterface::DELETE, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertEquals(MimeInterface::JSON, $request->getBody()->getContentType());
        $this->assertEquals(MimeInterface::JSON, $request->getExpectedType());
    }

    /**
     * HEAD запрос
     */
    public function testHead(): void
    {
        $request = Request::create();
        $request->head(
            'https://username:password@host.ru:8080/some/path/?foo=bar'
        );
        $this->assertEquals(HttpInterface::HEAD, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertNull($request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * OPTIONS запрос
     */
    public function testOptions(): void
    {
        $request = Request::create();
        $request->options(
            'https://username:password@host.ru:8080/some/path/?foo=bar'
        );
        $this->assertEquals(HttpInterface::OPTIONS, $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
        $this->assertNull($request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * URI запроса
     */
    public function testUri(): void
    {
        $request = Request::create();
        $request->withUri(new Uri('https://username:password@host.ru:8080/some/path/?foo=bar'));
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals(
            'https://username:password@host.ru:8080/some/path/?foo=bar',
            $request->getUri()->getUri()
        );
    }

    /**
     * URI запроса
     */
    public function testEmptyUri(): void
    {
        $request = Request::create();
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
    }

    /**
     * Установить mime
     */
    public function testMime(): void
    {
        $request = Request::create();
        $request->withMime('json');
        $this->assertEquals(MimeInterface::JSON, $request->getBody()->getContentType());
        $this->assertEquals(MimeInterface::JSON, $request->getExpectedType());
    }

    /**
     * Установить mime
     */
    public function testCustomMime(): void
    {
        $request = Request::create();
        $request->withMime('application/pdf');
        $this->assertEquals('application/pdf', $request->getBody()->getContentType());
        $this->assertEquals('application/pdf', $request->getExpectedType());
    }

    /**
     * Установить mime
     */
    public function testEmptyMime(): void
    {
        $request = Request::create();
        $request->withMime('');
        $this->assertNull($request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * Установить mime
     */
    public function testNullMime(): void
    {
        $request = Request::create();
        $request->withMime();
        $this->assertNull($request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * Установить content type
     */
    public function testContentType(): void
    {
        $request = Request::create();
        $request->getBody()->withContentType('json');
        $this->assertEquals(MimeInterface::JSON, $request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * Установить content type
     */
    public function testCustomContentType(): void
    {
        $request = Request::create();
        $request->getBody()->withContentType('application/pdf');
        $this->assertEquals('application/pdf', $request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * Установить content type
     */
    public function testEmptyContentType(): void
    {
        $request = Request::create();
        $request->getBody()->withContentType('');
        $this->assertNull($request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * Установить content type
     */
    public function testNullContentType(): void
    {
        $request = Request::create();
        $request->getBody()->withContentType();
        $this->assertNull($request->getBody()->getContentType());
        $this->assertNull($request->getExpectedType());
    }

    /**
     * Установить expected type
     */
    public function testExpectedType(): void
    {
        $request = Request::create();
        $request->withExpectedType('json');
        $this->assertEquals(MimeInterface::JSON, $request->getExpectedType());
        $this->assertNull($request->getBody()->getContentType());
    }

    /**
     * Установить expected type
     */
    public function testCustomExpectedType(): void
    {
        $request = Request::create();
        $request->withExpectedType('application/pdf');
        $this->assertEquals('application/pdf', $request->getExpectedType());
        $this->assertNull($request->getBody()->getContentType());
    }

    /**
     * Установить expected type
     */
    public function testEmptyExpectedType(): void
    {
        $request = Request::create();
        $request->withExpectedType('');
        $this->assertNull($request->getExpectedType());
        $this->assertNull($request->getBody()->getContentType());
    }

    /**
     * Установить expected type
     */
    public function testNullExpectedType(): void
    {
        $request = Request::create();
        $request->withExpectedType();
        $this->assertNull($request->getExpectedType());
        $this->assertNull($request->getBody()->getContentType());
    }

    /**
     * Промежуточное ПО у запроса
     */
    public function testWithMiddleware(): void
    {
        $request = Request::create();
        $this->assertInstanceOf(MiddlewareCollectionInterface::class, $request->getMiddlewares());
        $this->assertCount(0, $request->getMiddlewares());
        $request->withMiddleware(new Set500StatusMiddleware(), 100);
        $request->withMiddleware(new StopMiddleware(), 600);
        $this->assertInstanceOf(MiddlewareCollectionInterface::class, $request->getMiddlewares());
        $this->assertCount(2, $request->getMiddlewares());
    }

    /**
     * Использовании прокси в запросе
     */
    public function testWithProxy(): void
    {
        $request = Request::create();
        $this->assertNull($request->getProxy());
        $request->withProxy(new HttpProxy('127.0.0.1', 5000));
        $this->assertInstanceOf(ProxyInterface::class, $request->getProxy());
        $request->withProxy(null);
        $this->assertNull($request->getProxy());
    }

    /**
     * Добавление куки
     */
    public function testWithCookie(): void
    {
        $request = Request::create();
        $cookie = $request->withCookie('cookieName1', 'cookieValue1');
        $this->assertInstanceOf(CookieInterface::class, $cookie);
        $this->assertEquals('', $cookie->getDomain());
        $request->withUri(new Uri('https://domain.ru'));
        $this->assertEquals('domain.ru', $cookie->getDomain());
    }
}
