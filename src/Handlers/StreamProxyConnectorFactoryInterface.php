<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\ConfigInterface;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Фабрика для обработчиков proxy
 */
interface StreamProxyConnectorFactoryInterface
{
    /**
     * Фабричеый метод
     *
     * @param resource $context
     */
    public function factory(
        $context,
        ConfigInterface $config,
        RequestInterface &$request,
        ResponseInterface &$response,
        ProxyInterface $proxy
    ): StreamProxyConnectorInterface;
}
