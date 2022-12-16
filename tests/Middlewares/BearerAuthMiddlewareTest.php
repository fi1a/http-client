<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Middlewares\BearerAuthMiddleware;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;
use InvalidArgumentException;

class BearerAuthMiddlewareTest extends ServerTestCase
{
    /**
     * Успешная авторизация
     *
     * @dataProvider clientDataProvider
     */
    public function testAuthSuccess(HttpClientInterface $client): void
    {
        $client->withMiddleware(new BearerAuthMiddleware('123'));
        $response = $client->get('https://' . self::HOST . '/bearer-auth/');
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * 401 при аторизации
     *
     * @dataProvider clientDataProvider
     */
    public function testAuthNotSuccess(HttpClientInterface $client): void
    {
        $client->withMiddleware(new BearerAuthMiddleware('unknown'));
        $response = $client->get('https://' . self::HOST . '/bearer-auth/');
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Исключение при пустом токене
     */
    public function testEmptyTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BearerAuthMiddleware('');
    }
}
