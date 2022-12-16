<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Middlewares\RetryMiddleware;
use Fi1a\Unit\HttpClient\TestCase\ServerTestCase;
use InvalidArgumentException;

/**
 * Повторная отправка запросов при ошибке
 */
class RetryMiddlewareTest extends ServerTestCase
{
    /**
     * Успешная повторная попытка
     *
     * @dataProvider clientDataProvider
     */
    public function testSuccessRetry(HttpClientInterface $client): void
    {
        $client->withMiddleware(new RetryMiddleware(3));
        $response = $client->get('https://' . self::HOST . '/retry-success/');
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Успешная повторная попытка с функцией паузы
     *
     * @dataProvider clientDataProvider
     */
    public function testSuccessRetryCustomDelay(HttpClientInterface $client): void
    {
        $client->withMiddleware(new RetryMiddleware(3, function (int $try) {
            return $try;
        }));
        $response = $client->get('https://' . self::HOST . '/retry-success/');
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Не успешная повторная попытка
     *
     * @dataProvider clientDataProvider
     */
    public function testNotSuccessRetry(HttpClientInterface $client): void
    {
        $client->withMiddleware(new RetryMiddleware(3));
        $response = $client->get('https://' . self::HOST . '/retry-not-success/');
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Исключение при меньшем или равном 0 числе попыток
     */
    public function testRetryCountException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RetryMiddleware(0);
    }
}
