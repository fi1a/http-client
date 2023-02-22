<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Fixtures\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\Middlewares\AbstractMiddleware;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Промежуточное ПО для запроса (неизвестное сжатие)
 */
class UnknownContentEncodingMiddleware extends AbstractMiddleware
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
        $response = $response->withHeader('Content-Encoding', 'unknown');

        return $next($request, $response, $httpClient);
    }
}
