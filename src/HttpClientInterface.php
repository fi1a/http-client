<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\RequestMiddleware\RequestMiddlewareInterface;
use Fi1a\HttpClient\ResponseMiddleware\ResponseMiddlewareInterface;

/**
 * HTTP-client
 */
interface HttpClientInterface
{
    public function __construct(ConfigInterface $config, string $handler);

    /**
     * Добавить промежуточное ПО для запроса
     *
     * @return $this
     */
    public function addRequestMiddleware(RequestMiddlewareInterface $middleware);

    /**
     * Добавить промежуточное ПО для ответа
     *
     * @return $this
     */
    public function addResponseMiddleware(ResponseMiddlewareInterface $middleware);

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
    public function post($uri, $body = null, ?string $mime = null): ResponseInterface;

    /**
     * HTTP Метод Put
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     */
    public function put($uri, $body = null, ?string $mime = null): ResponseInterface;

    /**
     * HTTP Метод Patch
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     */
    public function patch($uri, $body = null, ?string $mime = null): ResponseInterface;

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
}
