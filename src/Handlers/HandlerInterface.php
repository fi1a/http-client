<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\ConfigInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Интерфейс обработчика запросов
 */
interface HandlerInterface
{
    public function __construct(ConfigInterface $config);

    /**
     * Отправляет запрос
     */
    public function send(RequestInterface $request, ResponseInterface $response): ResponseInterface;
}
