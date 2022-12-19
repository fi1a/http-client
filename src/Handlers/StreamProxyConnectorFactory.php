<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\ConfigInterface;
use Fi1a\HttpClient\Proxy\HttpProxy;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\Proxy\Socks5Proxy;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;
use LogicException;

/**
 * Фабрика для обработчиков proxy
 */
class StreamProxyConnectorFactory implements StreamProxyConnectorFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function factory(
        $context,
        ConfigInterface $config,
        RequestInterface $request,
        ResponseInterface $response,
        ProxyInterface $proxy
    ): StreamProxyConnectorInterface {
        if ($proxy instanceof HttpProxy) {
            return new HttpStreamProxyConnector($context, $config, $request, $response, $proxy);
        } elseif ($proxy instanceof Socks5Proxy) {
            return new Socks5StreamProxyConnector($context, $config, $request, $response, $proxy);
        }

        throw new LogicException('Неизвестный тип прокси');
    }
}
