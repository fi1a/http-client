<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Middlewares\BasicAuthMiddleware;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;

/**
 * Basic Auth
 */
class BasicAuthMiddlewareTest extends ServerTestCase
{
    /**
     * Успешная авторизация
     *
     * @dataProvider clientDataProvider
     */
    public function testAuthSuccess(HttpClientInterface $client): void
    {
        $client->withMiddleware(new BasicAuthMiddleware('test', 'test'));
        $response = $client->get('https://' . self::HOST . '/basic-auth/');
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
        $client->withMiddleware(new BasicAuthMiddleware('test', ''));
        $response = $client->get('https://' . self::HOST . '/basic-auth/');
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(401, $response->getStatusCode());
    }
}
