<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\TestCase;

use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\CurlHandler;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\HttpClientInterface;
use PHPUnit\Framework\TestCase;

/**
 * Тесты с обращением к серверу
 */
class ServerTestCase extends TestCase
{
    protected const HOST = WEB_SERVER_HOST . ':' . WEB_SERVER_PORT;

    /**
     * Возвращает HTTP-client
     */
    protected function getStreamClient(): HttpClientInterface
    {
        return new HttpClient(new Config(['ssl_verify' => false]), StreamHandler::class);
    }

    /**
     * Возвращает HTTP-client
     */
    protected function getCurlClient(): HttpClientInterface
    {
        return new HttpClient(new Config(['ssl_verify' => false]), CurlHandler::class);
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
            [
                $this->getCurlClient(),
            ],
        ];
    }
}
