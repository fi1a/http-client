<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Cookie\Cookie;
use Fi1a\HttpClient\Cookie\CookieInterface;
use Fi1a\HttpClient\Cookie\CookieStorage;
use Fi1a\HttpClient\Cookie\CookieStorageInterface;
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
    private $middlewares = [];

    /**
     * @var CookieStorageInterface
     */
    private $cookieStorage;

    public function __construct(
        ConfigInterface $config,
        string $handler,
        ?CookieStorageInterface $cookieStorage = null
    ) {
        if (!is_subclass_of($handler, HandlerInterface::class)) {
            throw new InvalidArgumentException(
                'Обработчик запросов должен реализовывать интерфейс ' . HandlerInterface::class
            );
        }
        $this->config = $config;
        $this->handler = $handler;
        if (!$cookieStorage) {
            $cookieStorage = new CookieStorage();
        }
        $this->cookieStorage = $cookieStorage;
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function addMiddleware(MiddlewareInterface $middleware, int $sort = 500)
    {
        $this->middlewares[] = [$middleware, $sort,];

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
        $this->setCookieToRequest($request);

        if ($this->callRequestMiddlewares($request, $response) === false) {
            return $response;
        }

        $instance = $this->factoryHandler();

        $response = $instance->send($request, $response);

        $this->setCookieToResponse($request, $response);

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
     * @inheritDoc
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
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
        foreach ($this->sortMiddlewares($this->middlewares) as $item) {
            [$middleware,] = $item;
            assert($middleware instanceof MiddlewareInterface);
            if ($middleware->handleRequest($request, $response, $this) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Вызывает промежуточное ПО ответа
     */
    private function callResponseMiddlewares(RequestInterface $request, ResponseInterface $response): bool
    {
        foreach ($this->sortMiddlewares($this->middlewares) as $item) {
            [$middleware,] = $item;
            assert($middleware instanceof MiddlewareInterface);
            if ($middleware->handleResponse($request, $response, $this) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Сортирует промежуточное ПО
     *
     * @param MiddlewareInterface[][]|int[][] $middlewares
     *
     * @return MiddlewareInterface[][]|int[][]
     */
    private function sortMiddlewares(array $middlewares): array
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

        return $middlewares;
    }

    /**
     * Устанавливает куки для ответа
     */
    private function setCookieToResponse(RequestInterface $request, ResponseInterface $response): void
    {
        if (!$this->config->getCookie()) {
            return;
        }

        if ($response->hasHeader('Set-Cookie')) {
            foreach ($response->getHeader('Set-Cookie') as $header) {
                assert($header instanceof HeaderInterface);
                $value = $header->getValue();
                if (!$value) {
                    continue;
                }
                $cookie = Cookie::fromString($value);
                if (!$cookie->getDomain()) {
                    $cookie->setDomain($request->getUri()->getHost());
                }
                if (mb_strpos($cookie->getPath(), '/') !== 0) {
                    $cookie->setPath($this->getCookiePath($request->getUri()->getPath()));
                }

                $this->cookieStorage->addCookie($cookie);
            }
        }

        $response->withCookies(
            $this->cookieStorage->getCookiesWithCondidition(
                $request->getUri()->getHost(),
                $request->getUri()->getPath()
            )
        );
    }

    /**
     * Устанавливает куки для запроса
     */
    private function setCookieToRequest(RequestInterface $request): void
    {
        if (!$this->config->getCookie()) {
            return;
        }

        $request->withCookies(
            $this->cookieStorage->getCookiesWithCondidition(
                $request->getUri()->getHost(),
                $request->getUri()->getPath(),
                $request->getUri()->getScheme()
            )
        );

        $cookies = [];
        foreach ($request->getCookies()->getValid() as $cookie) {
            assert($cookie instanceof CookieInterface);
            /**
             * @var string $name
             */
            $name = $cookie->getName();
            /**
             * @var string $value
             */
            $value = $cookie->getValue();
            $cookies[] = $name . '=' . rawurlencode($value);
        }
        if (count($cookies)) {
            $request->withHeader('Cookie', implode('; ', $cookies));
        }
    }

    /**
     * Возвращает путь куки
     */
    private function getCookiePath(string $url): string
    {
        $lastSlashes = mb_strrpos($url, '/');
        if ($url === '' || mb_strpos($url, '/') !== 0 || $url === '/' || !$lastSlashes) {
            return '/';
        }

        return mb_substr($url, 0, $lastSlashes);
    }
}
