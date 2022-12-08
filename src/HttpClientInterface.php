<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * HTTP-client
 */
interface HttpClientInterface
{
    /**
     * Добавить промежуточное ПО для запроса
     *
     * @return $this
     */
    public function addRequestMiddleware(callable $middleware);

    /**
     * Добавить промежуточное ПО для ответа
     *
     * @return $this
     */
    public function addResponseMiddleware(callable $middleware);

    /**
     * Отправляет запрос
     */
    public function send(RequestInterface $request): ResponseInterface;
}
