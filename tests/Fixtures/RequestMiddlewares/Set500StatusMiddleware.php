<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Fixtures\RequestMiddlewares;

use Fi1a\HttpClient\Middlewares\MiddlewareInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Промежуточное ПО для запроса (останаливает запрос)
 */
class Set500StatusMiddleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(RequestInterface $request, ResponseInterface $response): bool
    {
        $response->withStatus(500);

        return true;
    }
}
