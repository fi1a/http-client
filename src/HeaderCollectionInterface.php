<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Collection\InstanceCollectionInterface;

/**
 * Коллекция заголовков
 *
 * @mixin HeaderInterface
 */
interface HeaderCollectionInterface extends InstanceCollectionInterface
{
    /**
     * Проверяет наличие заголовка с определенным именем
     */
    public function hasHeader(string $name): bool;

    /**
     * Возвращает заголовки с определенным именем
     */
    public function getHeader(string $name): HeaderCollectionInterface;

    /**
     * Возвращает первый найденный заголовок с определенным именем
     */
    public function getFirstHeader(string $name): ?HeaderInterface;

    /**
     * Возвращает последний найденный заголовок с определенным именем
     */
    public function getLastHeader(string $name): ?HeaderInterface;

    /**
     * Удаляет заголовок с определенным именем
     */
    public function withoutHeader(string $name): bool;
}
