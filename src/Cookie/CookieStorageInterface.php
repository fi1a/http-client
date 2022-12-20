<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

/**
 * Хранилище cookie
 */
interface CookieStorageInterface
{
    /**
     * Добавить cookie в хранилище
     */
    public function addCookie(CookieInterface $cookie): bool;

    /**
     * Удалить cookie из хранилища
     */
    public function deleteCookie(string $name, ?string $domain = null, ?string $path = null): void;

    /**
     * Возвращает коллекцию cookie для домена и пути
     */
    public function getCookiesWithCondition(
        string $domain,
        string $path,
        ?string $scheme = null
    ): CookieCollectionInterface;

    /**
     * Возвращает коллекцию cookie
     */
    public function getCookies(): CookieCollectionInterface;
}
