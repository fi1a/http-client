<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Proxy\ProxyInterface;

/**
 * Абстрактный класс конфигуратора curl proxy
 */
abstract class AbstractCurlProxyConfigurator implements CurlProxyConfiguratorInterface
{
    /**
     * @var ProxyInterface
     */
    protected $proxy;

    public function __construct(ProxyInterface $proxy)
    {
        $this->proxy = $proxy;
    }
}
