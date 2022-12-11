<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\RequestMiddleware\RequestMiddlewareInterface;
use Fi1a\HttpClient\ResponseMiddleware\ResponseMiddlewareInterface;
use InvalidArgumentException;

/**
 * HTTP-client
 */
class HttpClient implements HttpClientInterface
{
    /**
     * @var string
     */
    private $handler;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config, string $handler)
    {
        if (!is_subclass_of($handler, HandlerInterface::class)) {
            throw new InvalidArgumentException(
                'Обработчик запросов должен реализовывать интерфейс ' . HandlerInterface::class
            );
        }
        $this->config = $config;
        $this->handler = $handler;
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function addRequestMiddleware(RequestMiddlewareInterface $middleware)
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function addResponseMiddleware(ResponseMiddlewareInterface $middleware)
    {
    }

    /**
     * @inheritDoc
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        if (!$request->getUri()->getHost()) {
            throw new InvalidArgumentException('Не передан хост для запроса');
        }
        $this->addHeaders($request);
        $instance = $this->factoryHandler();

        return $instance->send($request);
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function get($uri, ?string $mime = null): ResponseInterface
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function post($uri, $payload = null, ?string $mime = null): ResponseInterface
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function put($uri, $payload = null, ?string $mime = null): ResponseInterface
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function patch($uri, $payload = null, ?string $mime = null): ResponseInterface
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function delete($uri, ?string $mime = null): ResponseInterface
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function head($uri): ResponseInterface
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function options($uri): ResponseInterface
    {
    }

    /**
     * Добавить заголовки
     */
    private function addHeaders(RequestInterface $request): void
    {
        if (!$request->hasHeader('Host')) {
            $request->withHeader('Host', $request->getUri()->getHost());
        }
        if (!$request->hasHeader('Connection')) {
            $request->withHeader('Connection', 'close');
        }
    }

    /**
     * Фабричный метод для обработчика запроса
     */
    private function factoryHandler(): HandlerInterface
    {
        /**
         * @var HandlerInterface $instance
         * @psalm-suppress InvalidStringClass
         */
        $instance = new $this->handler($this->config);

        return $instance;
    }
}
