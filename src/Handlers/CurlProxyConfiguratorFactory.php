<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Proxy\ProxyInterface;
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
        if ($proxy->getType() === 'http') {
            return new HttpCurlProxyConfigurator($proxy);
        } elseif ($proxy->getType() === 'socks5') {
            return new Socks5CurlProxyConfigurator($proxy);
        }

        throw new LogicException('Неизвестный тип прокси');
    }
}
