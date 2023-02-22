<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\UriInterface;
use Fi1a\HttpClient\Cookie\CookieStorageInterface;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\Middlewares\MiddlewareInterface;
use Fi1a\HttpClient\Proxy\ProxyInterface;

/**
 * HTTP-client
 */
interface HttpClientInterface
{
    public function __construct(
        ?ConfigInterface $config = null,
        string $handler = StreamHandler::class,
        ?CookieStorageInterface $cookieStorage = null
    );

    /**
     * Добавить промежуточное ПО
     *
     * @return $this
     */
    public function addMiddleware(MiddlewareInterface $middleware, ?int $sort = null);

    /**
     * Отправляет запрос
     */
    public function send(RequestInterface $request): ResponseInterface;

    /**
     * HTTP Метод Get
     *
     * @param string|UriInterface $uri
     */
    public function get($uri, ?string $mime = null): ResponseInterface;

    /**
     * HTTP Метод Post
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     */
    public function post(
        $uri,
        $body = null,
        ?string $mime = null,
        ?UploadFileCollectionInterface $files = null
    ): ResponseInterface;

    /**
     * HTTP Метод Put
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     */
    public function put(
        $uri,
        $body = null,
        ?string $mime = null,
        ?UploadFileCollectionInterface $files = null
    ): ResponseInterface;

    /**
     * HTTP Метод Patch
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     */
    public function patch(
        $uri,
        $body = null,
        ?string $mime = null,
        ?UploadFileCollectionInterface $files = null
    ): ResponseInterface;

    /**
     * HTTP Метод Delete
     *
     * @param string|UriInterface $uri
     */
    public function delete($uri, ?string $mime = null): ResponseInterface;

    /**
     * HTTP Метод Head
     *
     * @param string|UriInterface $uri
     */
    public function head($uri): ResponseInterface;

    /**
     * HTTP Метод Options
     *
     * @param string|UriInterface $uri
     */
    public function options($uri): ResponseInterface;

    /**
     * Возвращает конфигурацию
     */
    public function getConfig(): ConfigInterface;

    /**
     * Добавляет префикс к используемым адресам
     *
     * @return $this
     */
    public function setUrlPrefix(?string $urlPrefix);

    /**
     * Использовать прокси для всех соединений
     *
     * @return $this
     */
    public function setProxy(?ProxyInterface $proxy);

    /**
     * Возвращает прокси
     */
    public function getProxy(): ?ProxyInterface;

    /**
     * Возвращает используемый запрос
     */
    public function getRequest(): ?RequestInterface;
}
