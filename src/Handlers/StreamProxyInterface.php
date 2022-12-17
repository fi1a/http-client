<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Proxy\ProxyInterface;

/**
 * Обработчик proxy для StreamHandler
 */
interface StreamProxyInterface
{
    public function __construct(ProxyInterface $proxy);

    /**
     * Соединение
     *
     * @return resource
     */
    public function connect();
}
