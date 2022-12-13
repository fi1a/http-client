<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Промежуточное ПО
 */
interface MiddlewareInterface
{
    /**
     * Обработчик для запроса
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ): bool;

    /**
     * Обработчик для ответа
     */
    public function handleResponse(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ): bool;
}
