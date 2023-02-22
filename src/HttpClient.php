<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Closure;
use Fi1a\Http\HeaderInterface;
use Fi1a\Http\MimeInterface;
use Fi1a\Http\Uri;
use Fi1a\Http\UriInterface;
use Fi1a\HttpClient\Cookie\Cookie;
use Fi1a\HttpClient\Cookie\CookieCollectionInterface;
use Fi1a\HttpClient\Cookie\CookieInterface;
use Fi1a\HttpClient\Cookie\CookieStorage;
use Fi1a\HttpClient\Cookie\CookieStorageInterface;
use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\Middlewares\Exceptions\StopException;
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
    protected $handler;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var MiddlewareCollectionInterface
     */
    protected $middlewares;

    /**
     * @var CookieStorageInterface
     */
    protected $cookieStorage;

    /**
     * @var string|null
     */
    protected $urlPrefix;

    /**
     * @var ProxyInterface|null
     */
    protected $proxy;

    /**
     * @var RequestInterface|null
     */
    protected $request;

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
    public function addMiddleware(MiddlewareInterface $middleware, ?int $sort = null)
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
        if ($this->urlPrefix) {
            $request = $request->withUri($this->getUrlPrefix($request->getUri()));
        }

        if (!$request->getUri()->host()) {
            throw new InvalidArgumentException('Не передан хост для запроса');
        }

        if ($this->getProxy() && !$request->getProxy()) {
            $request = $request->withProxy($this->getProxy());
        }

        $response = new Response();

        $request = $this->addDefaultHeaders($request);
        $request = $this->addAcceptHeaders($request);
        $request = $this->addContentHeaders($request);
        $request = $this->setCookieToRequest($request);

        try {
            $this->request = $request = $this->callRequestMiddlewares($request, $response);
            $instance = $this->factoryHandler();
            $response = $instance->send($request, $response);
            $response = $this->setCookieToResponse($request, $response);
        } catch (StopException $exception) {
        }

        return $this->callResponseMiddlewares($request, $response);
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
    public function setUrlPrefix(?string $urlPrefix)
    {
        $this->urlPrefix = $urlPrefix;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setProxy(?ProxyInterface $proxy)
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
     * @inheritDoc
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    /**
     * Добавить заголовки
     */
    protected function addDefaultHeaders(RequestInterface $request): RequestInterface
    {
        if (!$request->hasHeader('Host')) {
            $request = $request->withHeader('Host', $request->getUri()->host());
        }
        if (!$request->hasHeader('Connection')) {
            $request = $request->withHeader('Connection', 'close');
        }

        return $request;
    }

    /**
     * Добавить заголовки
     */
    protected function addAcceptHeaders(RequestInterface $request): RequestInterface
    {
        if (!$request->hasHeader('Accept') && $request->getExpectedType()) {
            $request = $request->withHeader('Accept', (string) $request->getExpectedType());
        }
        $compress = $this->config->getCompress();
        if ($compress && !$request->hasHeader('Accept-Encoding')) {
            $request = $request->withHeader('Accept-Encoding', $compress);
        }

        return $request;
    }

    /**
     * Добавить заголовки
     */
    protected function addContentHeaders(RequestInterface $request): RequestInterface
    {
        if (!$request->hasHeader('Content-Type') && $request->getBody()->has()) {
            $contentTypeHeader = $request->getBody()->getContentTypeHeader();
            if (!$contentTypeHeader) {
                $contentTypeHeader = MimeInterface::PLAIN;
            }
            $request = $request->withHeader('Content-Type', $contentTypeHeader);
        }
        if (!$request->hasHeader('Content-Length') && $request->getBody()->getSize()) {
            $request = $request->withHeader('Content-Length', (string) $request->getBody()->getSize());
        }

        return $request;
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
     * Следующий middleware для запроса
     */
    protected function nextRequestMiddleware(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClient $httpClient,
        int $index
    ): RequestInterface {
        $middlewares = $this->middlewares->merge($request->getMiddlewares());
        /** @var MiddlewareInterface|null $middleware */
        $middleware = $middlewares->sortDirect()->get($index);

        if (!$middleware) {
            return $request;
        }

        $next = Closure::bind(function (
            RequestInterface $request,
            ResponseInterface $response,
            HttpClient $httpClient
        ) use ($index): RequestInterface {
            $index++;

            return $this->nextRequestMiddleware($request, $response, $httpClient, $index);
        }, $this);

        return $middleware->handleRequest($request, $response, $this, $next);
    }

    /**
     * Вызывает промежуточное ПО запросов
     */
    protected function callRequestMiddlewares(RequestInterface $request, ResponseInterface $response): RequestInterface
    {
        return $this->nextRequestMiddleware($request, $response, $this, 0);
    }

    /**
     * Следующий middleware для ответа
     */
    protected function nextResponseMiddleware(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClient $httpClient,
        int $index
    ): ResponseInterface {
        $middlewares = $this->middlewares->merge($request->getMiddlewares());
        /** @var MiddlewareInterface|null $middleware */
        $middleware = $middlewares->sortBack()->get($index);

        if (!$middleware) {
            return $response;
        }

        $next = Closure::bind(function (
            RequestInterface $request,
            ResponseInterface $response,
            HttpClient $httpClient
        ) use ($index): ResponseInterface {
            $index++;

            return $this->nextResponseMiddleware(
                $request,
                $response,
                $httpClient,
                $index
            );
        }, $this);

        return $middleware->handleResponse($request, $response, $this, $next);
    }

    /**
     * Вызывает промежуточное ПО ответа
     */
    protected function callResponseMiddlewares(
        RequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $this->nextResponseMiddleware($request, $response, $this, 0);
    }

    /**
     * Устанавливает куки для ответа
     */
    protected function setCookieToResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->config->getCookie()) {
            return $response;
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
                    $cookie->setDomain($request->getUri()->host());
                }
                if (mb_strpos($cookie->getPath(), '/') !== 0) {
                    $cookie->setPath($this->getCookiePath($request->getUri()->path()));
                }

                $this->cookieStorage->addCookie($cookie);
            }
        }

        return $response->withCookies(
            $this->cookieStorage->getCookiesWithCondition(
                $request->getUri()->host(),
                $request->getUri()->path()
            )
        );
    }

    /**
     * Устанавливает куки для запроса
     */
    protected function setCookieToRequest(RequestInterface $request): RequestInterface
    {
        if (!$this->config->getCookie()) {
            return $request;
        }

        /**
         * @var CookieCollectionInterface $cookies
         */
        $cookies = $this->cookieStorage->getCookiesWithCondition(
            $request->getUri()->host(),
            $request->getUri()->path(),
            $request->getUri()->scheme()
        )->merge($request->getCookies());
        $request = $request->withCookies($cookies);

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
            $request = $request->withHeader('Cookie', implode('; ', $cookies));
        }

        return $request;
    }

    /**
     * Возвращает путь куки
     */
    protected function getCookiePath(string $url): string
    {
        $lastSlashes = mb_strrpos($url, '/');
        if ($url === '' || mb_strpos($url, '/') !== 0 || $url === '/' || !$lastSlashes) {
            return '/';
        }

        return mb_substr($url, 0, $lastSlashes);
    }

    /**
     * Возвращает uri с установленныс префиксом для адреса
     */
    protected function getUrlPrefix(UriInterface $uri): UriInterface
    {
        /** @psalm-suppress PossiblyNullArgument */
        $prefixUri = new Uri($this->urlPrefix);
        if ($prefixUri->scheme()) {
            $uri = $uri->withScheme($prefixUri->scheme());
        }
        if ($prefixUri->userInfo()) {
            $uri = $uri->withUserInfo($prefixUri->user(), $prefixUri->password());
        }
        if ($prefixUri->host()) {
            $uri = $uri->withHost($prefixUri->host());
        }
        if ($prefixUri->port()) {
            $uri = $uri->withPort($prefixUri->port());
        }
        $prefixPath = $prefixUri->path();
        if ($prefixPath) {
            if (mb_substr($uri->path(), 0, 1) === '/') {
                $prefixPath = rtrim($prefixPath, '/');
            }
            $uri = $uri->withPath($prefixPath . $uri->path());
        }

        return $uri;
    }
}
