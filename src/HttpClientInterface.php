<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Handlers\HandlerInterface;
use Fi1a\HttpClient\RequestMiddleware\RequestMiddlewareInterface;
use Fi1a\HttpClient\ResponseMiddleware\ResponseMiddlewareInterface;

/**
 * HTTP-client
 */
interface HttpClientInterface
{
    public function __construct(HandlerInterface $handler);

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
     * @param mixed $payload
     */
    public function post($uri, $payload = null, ?string $mime = null): ResponseInterface;

    /**
     * HTTP Метод Put
     *
     * @param string|UriInterface $uri
     * @param mixed $payload
     */
    public function put($uri, $payload = null, ?string $mime = null): ResponseInterface;

    /**
     * HTTP Метод Patch
     *
     * @param string|UriInterface $uri
     * @param mixed $payload
     */
    public function patch($uri, $payload = null, ?string $mime = null): ResponseInterface;

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
