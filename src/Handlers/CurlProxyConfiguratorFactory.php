<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Proxy\HttpProxy;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\Proxy\Socks5Proxy;
use LogicException;

/**
 * Фабрика для обработчиков proxy
 */
class CurlProxyConfiguratorFactory implements CurlProxyConfiguratorFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function factory(ProxyInterface $proxy): CurlProxyConfiguratorInterface
    {
        if ($proxy instanceof HttpProxy) {
            return new HttpCurlProxyConfigurator($proxy);
        } elseif ($proxy instanceof Socks5Proxy) {
            return new Socks5CurlProxyConfigurator($proxy);
        }

        throw new LogicException('Неизвестный тип прокси');
    }
}
