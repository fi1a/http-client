<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Объект ответа
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Код статуса
     */
    public function getStatusCode(): int;

    /**
     * Текст причины ассоциированный с кодом статуса
     */
    public function getReasonPhrase(): ?string;

    /**
     * Установить код статуса
     *
     * @return $this
     */
    public function withStatus(int $statusCode, string $reasonPhrase = '');

    /**
     * Запрос выполнен с ошибкой или нет
     */
    public function hasErrors(): bool;

    /**
     * Запрос выполнен успешно или нет
     */
    public function isSuccess(): bool;

    /**
     * Установить тело ответа
     *
     * @return $this
     */
    public function withBody(string $rawBody, ?string $mime = null);

    /**
     * Возвращает тело ответа
     */
    public function getBody(): ResponseBodyInterface;
}
