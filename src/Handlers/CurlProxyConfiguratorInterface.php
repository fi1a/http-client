<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Proxy\ProxyInterface;

/**
 * Обработчик proxy для CurlHandler
 */
interface CurlProxyConfiguratorInterface
{
    public function __construct(ProxyInterface $proxy);

    /**
     * Установка опций для прокси
     *
     * @param resource $resource
     */
    public function configure($resource): void;
}
