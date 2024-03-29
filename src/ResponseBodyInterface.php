<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Тело ответа
 */
interface ResponseBodyInterface extends BodyInterface
{
    /**
     * Установить тело ответа
     *
     * @return $this
     */
    public function setBody(string $raw, ?string $mime = null);

    /**
     * Возвращает тело ответа
     *
     * @return mixed
     */
    public function get();

    /**
     * Возвращает тело ответа без примененного преобразования
     */
    public function getRaw(): string;

    /**
     * Есть тело ответа или нет
     */
    public function has(): bool;
}
