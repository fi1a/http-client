<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Промежуточное ПО для запроса
 */
interface MiddlewareInterface
{
    /**
     * Промежуточное ПО для запроса
     */
    public function process(RequestInterface $request, ResponseInterface $response): bool;
}
