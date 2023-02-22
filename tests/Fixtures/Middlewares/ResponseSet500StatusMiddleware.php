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
class ResponseSet500StatusMiddleware extends AbstractMiddleware
{
    /**
     * @inheritDoc
     */
    public function handleResponse(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient,
        callable $next
    ): ResponseInterface {
        $response = $response->withStatus(500);

        return $next($request, $response, $httpClient);
    }
}
