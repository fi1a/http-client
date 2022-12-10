<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Тело запроса
 */
interface RequestBodyInterface extends BodyInterface
{
    /**
     * Установить тело запроса
     *
     * @param mixed $raw
     */
    public function withBody($raw, ?string $mime = null): void;

    /**
     * Возвращает тело запроса
     */
    public function get(): string;

    /**
     * Возвращает тело запроса без примененного преобразования
     *
     * @return mixed
     */
    public function getRaw();

    /**
     * Возвращает размер тела запроса
     */
    public function getSize(): int;

    /**
     * Есть тело запроса или нет
     */
    public function has(): bool;
}
