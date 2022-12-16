<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;
use InvalidArgumentException;

/**
 * Авторизация по ключу
 */
class ApiKeyAuthMiddleware extends AbstractMiddleware
{
    public const IN_HEADER = 'header';

    public const IN_QUERY = 'query';

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $place;

    public function __construct(string $key, string $value, string $place = self::IN_HEADER)
    {
        if (!$key) {
            throw new InvalidArgumentException('Ключ не может быть пустым');
        }
        $place = mb_strtolower($place);
        if (!in_array($place, [self::IN_HEADER, self::IN_QUERY])) {
            throw new InvalidArgumentException('Недопустимое значение места передачи токена');
        }
        $this->key = $key;
        $this->value = $value;
        $this->place = $place;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ) {
        if ($this->place === self::IN_HEADER) {
            $request->withHeader($this->key, $this->value);

            return true;
        }

        $queryParams = $request->getUri()->getQueryParams();
        $queryParams[$this->key] = $this->value;
        $request->getUri()->withQueryParams($queryParams);

        return true;
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
