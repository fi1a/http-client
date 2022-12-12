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
        $this->addDefaultHeaders($request);
        $this->addContentHeaders($request);
        $instance = $this->factoryHandler();

        return $instance->send($request);
    }

    /**
     * @inheritDoc
     */
    public function get($uri, ?string $mime = null): ResponseInterface
    {
        return $this->send(Request::create()->get($uri, $mime));
    }

    /**
     * @inheritDoc
     */
    public function post($uri, $body = null, ?string $mime = null): ResponseInterface
    {
        return $this->send(Request::create()->post($uri, $body, $mime));
    }

    /**
     * @inheritDoc
     */
    public function put($uri, $body = null, ?string $mime = null): ResponseInterface
    {
        return $this->send(Request::create()->put($uri, $body, $mime));
    }

    /**
     * @inheritDoc
     */
    public function patch($uri, $body = null, ?string $mime = null): ResponseInterface
    {
        return $this->send(Request::create()->patch($uri, $body, $mime));
    }

    /**
     * @inheritDoc
     */
    public function delete($uri, ?string $mime = null): ResponseInterface
    {
        return $this->send(Request::create()->delete($uri, $mime));
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
    private function addDefaultHeaders(RequestInterface $request): void
    {
        if (!$request->hasHeader('Host')) {
            $request->withHeader('Host', $request->getUri()->getHost());
        }
        if (!$request->hasHeader('Connection')) {
            $request->withHeader('Connection', 'close');
        }
    }

    /**
     * Добавить заголовки
     */
    private function addContentHeaders(RequestInterface $request): void
    {
        if (!$request->hasHeader('Accept') && $request->getExpectedType()) {
            $request->withHeader('Accept', (string) $request->getExpectedType());
        }
        if (!$request->hasHeader('Content-Type')) {
            $contentType = $request->getBody()->getContentType();
            if (!$contentType) {
                $contentType = MimeInterface::HTML;
                if ($request->getMethod() === HttpInterface::POST || $request->getMethod() === HttpInterface::PUT) {
                    $contentType = MimeInterface::FORM;
                }
            }
            $request->withHeader('Content-Type', $contentType);
        }
        if (!$request->hasHeader('Content-Length') && $request->getBody()->getSize()) {
            $request->withHeader('Content-Length', (string) $request->getBody()->getSize());
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
