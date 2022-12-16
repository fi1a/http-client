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
     * @return ResponseInterface|bool
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    );

    /**
     * Обработчик для ответа
     *
     * @return ResponseInterface|bool
     */
    public function handleResponse(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    );
}
