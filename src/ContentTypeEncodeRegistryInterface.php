<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\ContentTypeEncodes\ContentTypeEncodeInterface;

/**
 * Реестр парсеров типов контента
 */
interface ContentTypeEncodeRegistryInterface
{
    /**
     * Добавить парсер
     */
    public static function add(string $contentType, string $parser): void;

    /**
     * Проверяет наличие парсера
     */
    public static function has(string $contentType): bool;

    /**
     * Удаляет парсер
     */
    public static function delete(string $contentType): bool;

    /**
     * Возвращает класс парсера типа контента
     *
     * @return ContentTypeEncodeInterface|false
     */
    public static function get(string $contentType);
}
