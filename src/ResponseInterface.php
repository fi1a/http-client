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
     * Установить тело ответа
     *
     * @return $this
     */
    public function withBody(string $rawBody, ?string $mime = null);

    /**
     * Есть тело ответа или нет
     */
    public function hasBody(): bool;

    /**
     * Возвращает тело ответа
     *
     * @return mixed
     */
    public function getBody();

    /**
     * Возвращает тело ответа без примененного преобразования
     */
    public function getRawBody(): string;
}
