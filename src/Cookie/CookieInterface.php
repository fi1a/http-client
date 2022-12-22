<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Http\CookieInterface as HttpCookieInterface;

/**
 * Cookie
 */
interface CookieInterface extends HttpCookieInterface
{
    /**
     * Действует только на эту сессию
     */
    public function getSession(): bool;

    /**
     * Действует только на эту сессию
     *
     * @return $this
     */
    public function setSession(bool $secure);

    /**
     * Проверяет, соответствует ли домен
     */
    public function matchDomain(string $domain): bool;

    /**
     * Проверяет, соответствует ли пути
     */
    public function matchPath(string $path): bool;

    /**
     * Создать куку из строки
     *
     * @return static
     */
    public static function fromString(string $string);
}
