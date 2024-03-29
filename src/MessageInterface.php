<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\HeaderCollectionInterface;
use Fi1a\Http\HeaderInterface;
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
     * Возвращает кодировку
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
     * Удаляет заголовок с определенным именем
     *
     * @return $this
     */
    public function withoutHeader(string $name);

    /**
     * Удаляет все заголовки
     *
     * @return $this
     */
    public function clearHeaders();

    /**
     * Возвращает коллекцию cookies
     */
    public function getCookies(): CookieCollectionInterface;

    /**
     * Устанавливает коллекцию cookies
     *
     * @return $this
     */
    public function withCookies(CookieCollectionInterface $collection);
}
