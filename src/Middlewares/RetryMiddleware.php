<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\HttpClient\HttpClientInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;
use InvalidArgumentException;

/**
 * Повторная отправка запросов при ошибке
 */
class RetryMiddleware extends AbstractMiddleware
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $try = 0;

    /**
     * @var callable
     */
    private $delayFn;

    public function __construct(int $count = 1, ?callable $delayFn = null)
    {
        if (!$count) {
            throw new InvalidArgumentException('Число попыток должно быть больше нуля');
        }
        $this->count = $count;
        $this->delayFn = $delayFn ?? [$this, 'getExponentialDelay'];
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(
        RequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ) {
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
        if ($response->isSuccess() || $this->try >= $this->count) {
            return true;
        }

        $delay = (int) call_user_func_array($this->delayFn, [$this->try]);
        if ($delay > 0) {
            sleep($delay);
        }
        $this->try++;

        return $httpClient->send($request);
    }

    /**
     * Возвращает паузу между попытками запроса
     */
    private function getExponentialDelay(int $try): int
    {
        return (int) pow(2, $try - 1);
    }
}
