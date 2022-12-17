<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Middlewares\MiddlewareCollectionInterface;
use Fi1a\HttpClient\Middlewares\MiddlewareInterface;

/**
 * Объект запроса
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Создать объект запроса
     *
     * @return self
     */
    public static function create();

    /**
     * Метод запроса
     *
     * @return $this
     */
    public function withMethod(string $method);

    /**
     * Возвращает метод запроса
     */
    public function getMethod(): string;

    /**
     * HTTP Метод Get
     *
     * @param string|UriInterface $uri
     *
     * @return $this
     */
    public function get($uri, ?string $mime = null);

    /**
     * HTTP Метод Post
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     *
     * @return $this
     */
    public function post($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null);

    /**
     * HTTP Метод Put
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     *
     * @return $this
     */
    public function put($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null);

    /**
     * HTTP Метод Patch
     *
     * @param string|UriInterface $uri
     * @param mixed               $body
     *
     * @return $this
     */
    public function patch($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null);

    /**
     * HTTP Метод Delete
     *
     * @param string|UriInterface $uri
     *
     * @return $this
     */
    public function delete($uri, ?string $mime = null);

    /**
     * HTTP Метод Head
     *
     * @param string|UriInterface $uri
     *
     * @return $this
     */
    public function head($uri);

    /**
     * HTTP Метод Options
     *
     * @param string|UriInterface $uri
     *
     * @return $this
     */
    public function options($uri);

    /**
     * Возвращает URI запроса
     */
    public function getUri(): UriInterface;

    /**
     * Устанавливает URI запроса
     *
     * @return $this
     */
    public function withUri(UriInterface $uri);

    /**
     * Устанавливаем Content type и Expected type
     *
     * @return $this
     */
    public function withMime(?string $mime = null);

    /**
     * Устанавливаем expected type
     *
     * @return $this
     */
    public function withExpectedType(?string $mime = null);

    /**
     * Expected type
     */
    public function getExpectedType(): ?string;

    /**
     * Тело запроса
     *
     * @param mixed $body
     *
     * @return $this
     */
    public function withBody($body, ?string $mime = null, ?UploadFileCollectionInterface $files = null);

    /**
     * Возвращает payload
     */
    public function getBody(): RequestBodyInterface;

    /**
     * Добавить промежуточное ПО
     *
     * @return $this
     */
    public function withMiddleware(MiddlewareInterface $middleware, ?int $sort = null);

    /**
     *  Возвращает промежуточное ПО
     */
    public function getMiddlewares(): MiddlewareCollectionInterface;
}
