<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

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
     * @param mixed $payload
     *
     * @return $this
     */
    public function post($uri, $payload = null, ?string $mime = null);

    /**
     * HTTP Метод Put
     *
     * @param string|UriInterface $uri
     * @param mixed $payload
     *
     * @return $this
     */
    public function put($uri, $payload = null, ?string $mime = null);

    /**
     * HTTP Метод Patch
     *
     * @param string|UriInterface $uri
     * @param mixed $payload
     *
     * @return $this
     */
    public function patch($uri, $payload = null, ?string $mime = null);

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
     * @param mixed $payload
     *
     * @return $this
     */
    public function withBody($payload, ?string $mime = null);

    /**
     * Возвращает payload
     *
     * @return mixed
     */
    public function getPayload();
}
