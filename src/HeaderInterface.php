<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Заголовок
 */
interface HeaderInterface
{
    public function __construct(string $name, ?string $value = null);

    /**
     * Установить название заголовка
     */
    public function setName(string $name): void;

    /**
     * Вернуть название заголовка
     */
    public function getName(): string;

    /**
     * Установить значение заголовка
     */
    public function setValue(?string $value): void;

    /**
     * Вернуть значение заголовка
     */
    public function getValue(): ?string;

    /**
     * Возвращает строку заголовка
     */
    public function getLine(): string;
}
