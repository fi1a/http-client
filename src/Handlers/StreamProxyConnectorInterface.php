<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\ConfigInterface;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Обработчик proxy для StreamHandler
 */
interface StreamProxyConnectorInterface
{
    /**
     * @param resource $context
     */
    public function __construct(
        $context,
        ConfigInterface $config,
        RequestInterface $request,
        ResponseInterface $response,
        ProxyInterface $proxy
    );

    /**
     * Соединение
     *
     * @return resource
     */
    public function connect();
}
