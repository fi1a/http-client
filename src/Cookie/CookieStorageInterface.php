<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

/**
 * Хранилище кук
 */
interface CookieStorageInterface
{
    /**
     * Добавить куку в хранилище
     */
    public function addCookie(CookieInterface $cookie): bool;

    /**
     * Возвращает коллекцию кук для домена и пути
     */
    public function getCookiesWithCondition(
        string $domain,
        string $path,
        ?string $scheme = null
    ): CookieCollectionInterface;

    /**
     * Возвращает коллекцию кук
     */
    public function getCookies(): CookieCollectionInterface;
}
