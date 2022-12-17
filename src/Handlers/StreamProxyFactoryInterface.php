<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Proxy\ProxyInterface;

/**
 * Фабрика для обработчиков proxy
 */
interface StreamProxyFactoryInterface
{
    /**
     * Фабричеый метод
     */
    public function factory(ProxyInterface $proxy): StreamProxyInterface;
}
