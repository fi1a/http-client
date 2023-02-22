<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Basic авторизация
 */
class BasicAuthMiddleware extends AbstractMiddleware
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient,
        callable $next
    ): RequestInterface {
        $request = $request->withHeader(
            'Authorization',
            sprintf('Basic %s', base64_encode($this->username . ':' . $this->password))
        );

        return $next($request, $response, $httpClient);
    }
}
