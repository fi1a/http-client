<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Cookie\CookieCollectionInterface;

/**
 * Сообщение
 */
interface MessageInterface
{
    /**
     * Возвращает версию протокола HTTP
     */
    public function getProtocolVersion(): string;

    /**
     * Устанавливает версию протокола HTTP
     *
     * @return $this
     */
    public function withProtocolVersion(string $version);

    /**
     * Возвращает кодировку запроса
     */
    public function getEncoding(): string;

    /**
     * Устанавливает кодировку
     *
     * @return $this
     */
    public function withEncoding(string $encoding);

    /**
     * Возвращает коллекцию заголовков
     */
    public function getHeaders(): HeaderCollectionInterface;

    /**
     * Устанавливает коллекцию заголовков
     *
     * @return $this
     */
    public function withHeaders(HeaderCollectionInterface $headers);

    /**
     * Добавить заголовок к коллекции
     *
     * @return $this
     */
    public function addHeader(HeaderInterface $header);

    /**
     * Проверяет наличие заголовка с определенным именем
     */
    public function hasHeader(string $name): bool;

    /**
     * Возвращает заголовок с определенным именем
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
     * Добавляет заголовок с определенным именем и значением
     *
     * @return $this
     */
    public function withHeader(string $name, string $value);

    /**
     * Добавляет заголовок с определенным именем и значением и возвращает объект заголовка
     */
    public function withAddedHeader(string $name, string $value): HeaderInterface;

    /**
     * Удаляет заголовок с определенным именем
     */
    public function withoutHeader(string $name): bool;

    /**
     * Удаляет все заголовки
     */
    public function clearHeaders(): bool;

    /**
     * Возвращает коллекцию кук
     */
    public function getCookies(): CookieCollectionInterface;

    /**
     * Устанавливает коллекцию кук
     *
     * @return $this
     */
    public function withCookies(CookieCollectionInterface $collection);
}
