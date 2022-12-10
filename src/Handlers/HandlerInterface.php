<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Handlers\DTO\Response;
use Fi1a\HttpClient\RequestInterface;

/**
 * Интерфейс обработчика запросов
 */
interface HandlerInterface
{
    /**
     * Отправляет запрос
     */
    public function send(RequestInterface $request): Response;
}
