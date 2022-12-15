<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Basic Auth
 */
class BearerAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token)
    {
        if (!$token) {
            throw new \InvalidArgumentException('Токен не может быть пустым');
        }
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ): bool {
        $request->withHeader(
            'Authorization',
            sprintf('Bearer %s', $this->token)
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function handleResponse(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ): bool {
        return true;
    }
}
