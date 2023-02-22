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
     * Возвращает сортировку
     */
    public function getSort(): int;

    /**
     * Устанавливает сортировку
     *
     * @return $this
     */
    public function setSort(int $sort);

    /**
     * Обработчик для запроса
     *
     * @param callable(RequestInterface, ResponseInterface, HttpClientInterface): RequestInterface $next
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient,
        callable $next
    ): RequestInterface;

    /**
     * Обработчик для ответа
     *
     * @param callable(RequestInterface, ResponseInterface, HttpClientInterface): ResponseInterface $next
     */
    public function handleResponse(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient,
        callable $next
    ): ResponseInterface;
}
