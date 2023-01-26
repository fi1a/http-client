<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\HeaderInterface;
use Fi1a\Http\MimeInterface;
use Fi1a\Http\Uri;
use Fi1a\HttpClient\Cookie\Cookie;
use Fi1a\HttpClient\Cookie\CookieCollectionInterface;
use Fi1a\HttpClient\Cookie\CookieInterface;
use Fi1a\HttpClient\Cookie\CookieStorage;
use Fi1a\HttpClient\Cookie\CookieStorageInterface;
use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\Middlewares\MiddlewareCollection;
use Fi1a\HttpClient\Middlewares\MiddlewareCollectionInterface;
use Fi1a\HttpClient\Middlewares\MiddlewareInterface;
use Fi1a\HttpClient\Proxy\ProxyInterface;
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
     * @var MiddlewareCollectionInterface
     */
    private $middlewares;

    /**
     * @var CookieStorageInterface
     */
    private $cookieStorage;

    /**
     * @var string|null
     */
    private $urlPrefix;

    /**
     * @var ProxyInterface|null
     */
    private $proxy;

    public function __construct(
        ?ConfigInterface $config = null,
        string $handler = StreamHandler::class,
        ?CookieStorageInterface $cookieStorage = null
    ) {
        if (is_null($config)) {
            $config = new Config();
        }
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
        $this->middlewares = new MiddlewareCollection();
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnType
     */
    public function withMiddleware(MiddlewareInterface $middleware, ?int $sort = null)
    {
        if (!is_null($sort)) {
            $middleware->setSort($sort);
        }
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $this->setUrlPrefix($request);

        if (!$request->getUri()->getHost()) {
            throw new InvalidArgumentException('Не передан хост для запроса');
        }

        if ($this->getProxy() && !$request->getProxy()) {
            $request->withProxy($this->getProxy());
        }

        $response = new Response();

        $this->addDefaultHeaders($request);
        $this->addAcceptHeaders($request);
        $this->addContentHeaders($request);
        $this->setCookieToRequest($request);

        /**
         * @var false|ResponseInterface $resultMiddleware
         */
        $resultMiddleware = $this->callRequestMiddlewares($request, $response);
        if (!$resultMiddleware) {
            return $response;
        }
        $response = $resultMiddleware;

        $instance = $this->factoryHandler();
        $response = $instance->send($request, $response);
        $this->setCookieToResponse($request, $response);

        /**
         * @var false|ResponseInterface $resultMiddleware
         */
        $resultMiddleware = $this->callResponseMiddlewares($request, $response);
        if (!$resultMiddleware) {
            return $response;
        }

        return $resultMiddleware;
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
    public function post(
        $uri,
        $body = null,
        ?string $mime = null,
        ?UploadFileCollectionInterface $files = null
    ): ResponseInterface {
        return $this->send(Request::create()->post($uri, $body, $mime, $files));
    }

    /**
     * @inheritDoc
     */
    public function put(
        $uri,
        $body = null,
        ?string $mime = null,
        ?UploadFileCollectionInterface $files = null
    ): ResponseInterface {
        return $this->send(Request::create()->put($uri, $body, $mime, $files));
    }

    /**
     * @inheritDoc
     */
    public function patch(
        $uri,
        $body = null,
        ?string $mime = null,
        ?UploadFileCollectionInterface $files = null
    ): ResponseInterface {
        return $this->send(Request::create()->patch($uri, $body, $mime, $files));
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
     * @inheritDoc
     */
    public function withUrlPrefix(?string $urlPrefix)
    {
        $this->urlPrefix = $urlPrefix;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withProxy(?ProxyInterface $proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getProxy(): ?ProxyInterface
    {
        return $this->proxy;
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
            $contentTypeHeader = $request->getBody()->getContentTypeHeader();
            if (!$contentTypeHeader) {
                $contentTypeHeader = MimeInterface::PLAIN;
            }
            $request->withHeader('Content-Type', $contentTypeHeader);
        }
        if (!$request->hasHeader('Content-Length') && $request->getBody()->getSize()) {
            $request->withHeader('Content-Length', (string) $request->getBody()->getSize());
        }
    }

    /**
     * Фабричный метод для обработчика запроса
     */
    protected function factoryHandler(): HandlerInterface
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
     *
     * @return ResponseInterface|bool
     */
    private function callRequestMiddlewares(RequestInterface $request, ResponseInterface $response)
    {
        $middlewares = $this->middlewares->merge($request->getMiddlewares());

        foreach ($middlewares->sortDirect() as $middleware) {
            assert($middleware instanceof MiddlewareInterface);
            $result = $middleware->handleRequest($request, $response, $this);
            if (!$result) {
                return false;
            }
            if ($result instanceof ResponseInterface) {
                $response = $result;
            }
        }

        return $response;
    }

    /**
     * Вызывает промежуточное ПО ответа
     *
     * @return ResponseInterface|bool
     */
    private function callResponseMiddlewares(RequestInterface $request, ResponseInterface $response)
    {
        $middlewares = $this->middlewares->merge($request->getMiddlewares());

        foreach ($middlewares->sortBack() as $middleware) {
            assert($middleware instanceof MiddlewareInterface);
            $result = $middleware->handleResponse($request, $response, $this);
            if (!$result) {
                return false;
            }
            if ($result instanceof ResponseInterface) {
                $response = $result;
            }
        }

        return $response;
    }

    /**
     * Устанавливает куки для ответа
     */
    private function setCookieToResponse(RequestInterface $request, ResponseInterface $response): void
    {
        if (!$this->config->getCookie()) {
            return;
        }

        foreach ($request->getCookies() as $cookie) {
            assert($cookie instanceof CookieInterface);
            $this->cookieStorage->addCookie($cookie);
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
            $this->cookieStorage->getCookiesWithCondition(
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

        /**
         * @var CookieCollectionInterface $cookies
         */
        $cookies = $this->cookieStorage->getCookiesWithCondition(
            $request->getUri()->getHost(),
            $request->getUri()->getPath(),
            $request->getUri()->getScheme()
        )->merge($request->getCookies());
        $request->withCookies($cookies);

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

    /**
     * Устанавливает префикс для адреса
     */
    private function setUrlPrefix(RequestInterface $request): void
    {
        if (!$this->urlPrefix) {
            return;
        }

        $prefixUri = new Uri($this->urlPrefix);
        $uri = $request->getUri();
        if ($prefixUri->getScheme()) {
            $uri->withScheme($prefixUri->getScheme());
        }
        if ($prefixUri->getUserInfo()) {
            $uri->withUserInfo($prefixUri->getUser(), $prefixUri->getPassword());
        }
        if ($prefixUri->getHost()) {
            $uri->withHost($prefixUri->getHost());
        }
        if ($prefixUri->getPort()) {
            $uri->withPort($prefixUri->getPort());
        }
        $prefixPath = $prefixUri->getPath();
        if ($prefixPath) {
            if (mb_substr($uri->getPath(), 0, 1) === '/') {
                $prefixPath = rtrim($prefixPath, '/');
            }
            $uri->withPath($prefixPath . $uri->getPath());
        }
    }
}
