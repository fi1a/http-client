<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Fixtures\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Middlewares\AbstractMiddleware;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Промежуточное ПО для запроса (останаливает запрос)
 */
class StopMiddleware extends AbstractMiddleware
{
    /**
     * @inheritDoc
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ) {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function handleResponse(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ) {
        return true;
    }
}
