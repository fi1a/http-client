<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\TestCase;

use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\CurlHandler;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Proxy\HttpProxy;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\Proxy\Socks5Proxy;
use PHPUnit\Framework\TestCase;

/**
 * Тесты с обращением к серверу
 */
class ServerTestCase extends TestCase
{
    protected const HOST = WEB_SERVER_HOST . ':' . WEB_SERVER_HTTPS_PORT;

    protected const HTTP_HOST = WEB_SERVER_HOST . ':' . WEB_SERVER_HTTP_PORT;

    /**
     * Возвращает HTTP-client
     */
    protected function getStreamClient(): HttpClientInterface
    {
        return new HttpClient(new Config(['sslVerify' => false]), StreamHandler::class);
    }

    /**
     * Возвращает HTTP-client
     */
    protected function getCurlClient(): HttpClientInterface
    {
        return new HttpClient(new Config(['sslVerify' => false]), CurlHandler::class);
    }

    /**
     * Http proxy
     */
    protected function getHttpProxy(): ProxyInterface
    {
        return new HttpProxy(
            (string) HTTP_PROXY_HOST,
            (int) HTTP_PROXY_PORT,
            HTTP_PROXY_USERNAME,
            HTTP_PROXY_PASSWORD
        );
    }

    /**
     * Socks5 proxy
     */
    protected function getSocks5Proxy(): ProxyInterface
    {
        return new Socks5Proxy(
            (string) SOCKS5_PROXY_HOST,
            (int) SOCKS5_PROXY_PORT,
            SOCKS5_PROXY_USERNAME,
            SOCKS5_PROXY_PASSWORD
        );
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
