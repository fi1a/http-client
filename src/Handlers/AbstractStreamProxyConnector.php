<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\ConfigInterface;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Абстрактный класс обработчика stream proxy
 */
abstract class AbstractStreamProxyConnector implements StreamProxyConnectorInterface
{
    /**
     * @var ProxyInterface
     */
    protected $proxy;

    /**
     * @var resource
     */
    protected $context;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @inheritDoc
     */
    public function __construct(
        $context,
        ConfigInterface $config,
        RequestInterface &$request,
        ResponseInterface &$response,
        ProxyInterface $proxy
    ) {
        $this->context = $context;
        $this->proxy = $proxy;
        $this->config = $config;
        $this->request = &$request;
        $this->response = &$response;
    }
}
