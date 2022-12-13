<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\Middlewares\MiddlewareInterface;
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

    /**
     * @var MiddlewareInterface[][]|int[][]
     */
    private $requestMiddlewares = [];

    /**
     * @var MiddlewareInterface[][]|int[][]
     */
    private $responseMiddlewares = [];

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
    public function addRequestMiddleware(MiddlewareInterface $middleware, int $sort = 500)
    {
        $this->requestMiddlewares[] = [$middleware, $sort,];

        return $this;
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function addResponseMiddleware(MiddlewareInterface $middleware, int $sort = 500)
    {
        $this->responseMiddlewares[] = [$middleware, $sort,];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        if (!$request->getUri()->getHost()) {
            throw new InvalidArgumentException('Не передан хост для запроса');
        }

        $response = new Response();

        $this->addDefaultHeaders($request);
        $this->addAcceptHeaders($request);
        $this->addContentHeaders($request);

        if ($this->callRequestMiddlewares($request, $response) === false) {
            return $response;
        }

        $instance = $this->factoryHandler();

        $response = $instance->send($request, $response);

        if ($this->callResponseMiddlewares($request, $response) === false) {
            return $response;
        }

        return $response;
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
     */
    public function head($uri): ResponseInterface
    {
        return $this->send(Request::create()->head($uri));
    }

    /**
     * @inheritDoc
     */
    public function options($uri): ResponseInterface
    {
        return $this->send(Request::create()->options($uri));
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
    private function addAcceptHeaders(RequestInterface $request): void
    {
        if (!$request->hasHeader('Accept') && $request->getExpectedType()) {
            $request->withHeader('Accept', (string) $request->getExpectedType());
        }
        $compress = $this->config->getCompress();
        if ($compress && !$request->hasHeader('Accept-Encoding')) {
            $request->withHeader('Accept-Encoding', $compress);
        }
    }

    /**
     * Добавить заголовки
     */
    private function addContentHeaders(RequestInterface $request): void
    {
        if (!$request->hasHeader('Content-Type') && $request->getBody()->has()) {
            $contentType = $request->getBody()->getContentType();
            if (!$contentType) {
                $contentType = MimeInterface::PLAIN;
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

    /**
     * Вызывает промежуточное ПО запросов
     */
    private function callRequestMiddlewares(RequestInterface $request, ResponseInterface $response): bool
    {
        return $this->callMiddlewares($this->requestMiddlewares, $request, $response);
    }

    /**
     * Вызывает промежуточное ПО ответа
     */
    private function callResponseMiddlewares(RequestInterface $request, ResponseInterface $response): bool
    {
        return $this->callMiddlewares($this->responseMiddlewares, $request, $response);
    }

    /**
     * Вызывает промежуточное ПО
     *
     * @param MiddlewareInterface[][]|int[][] $middlewares
     */
    private function callMiddlewares(array $middlewares, RequestInterface $request, ResponseInterface $response): bool
    {
        usort(
            $middlewares, /**
            @psalm-param $itemA array<array-key, Fi1a\HttpClient\Middlewares\MiddlewareInterface|int>
            @psalm-param $itemB array<array-key, Fi1a\HttpClient\Middlewares\MiddlewareInterface|int>
            */
            function (array $itemA, array $itemB): int {
                return (int) $itemA[1] - (int) $itemB[1];
            }
        );
        foreach ($middlewares as $item) {
            [$middleware,] = $item;
            assert($middleware instanceof MiddlewareInterface);
            if ($middleware->process($request, $response) === false) {
                return false;
            }
        }

        return true;
    }
}
