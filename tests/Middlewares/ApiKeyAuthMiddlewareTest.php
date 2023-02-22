<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Middlewares\ApiKeyAuthMiddleware;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;
use InvalidArgumentException;

/**
 * Авторизация по ключу
 */
class ApiKeyAuthMiddlewareTest extends ServerTestCase
{
    /**
     * Успешная авторизация с ключем в заголовке
     *
     * @dataProvider clientDataProvider
     */
    public function testAuthSuccessKeyInHeader(HttpClientInterface $client): void
    {
        $client->addMiddleware(new ApiKeyAuthMiddleware('token', '123'));
        $response = $client->get('https://' . self::HOST . '/api-key-auth/');
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * 401 при аторизации с ключем в заголовке
     *
     * @dataProvider clientDataProvider
     */
    public function testAuthNotSuccessKeyInHeader(HttpClientInterface $client): void
    {
        $client->addMiddleware(new ApiKeyAuthMiddleware('token', 'unknown'));
        $response = $client->get('https://' . self::HOST . '/api-key-auth/');
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Успешная авторизация с ключем в заголовке
     *
     * @dataProvider clientDataProvider
     */
    public function testAuthSuccessKeyInQuery(HttpClientInterface $client): void
    {
        $client->addMiddleware(
            new ApiKeyAuthMiddleware('token', '123', ApiKeyAuthMiddleware::IN_QUERY)
        );
        $response = $client->get('https://' . self::HOST . '/api-key-auth/');
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * 401 при аторизации с ключем в заголовке
     *
     * @dataProvider clientDataProvider
     */
    public function testAuthNotSuccessKeyInQuery(HttpClientInterface $client): void
    {
        $client->addMiddleware(
            new ApiKeyAuthMiddleware('token', 'unknown', ApiKeyAuthMiddleware::IN_QUERY)
        );
        $response = $client->get('https://' . self::HOST . '/api-key-auth/');
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Исключение при пустом ключе
     */
    public function testKeyException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ApiKeyAuthMiddleware('', 'unknown');
    }

    /**
     * Исключение при ошибочном месте передачи ключа
     */
    public function testPlaceException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ApiKeyAuthMiddleware('api', 'unknown', 'unknown');
    }
}
