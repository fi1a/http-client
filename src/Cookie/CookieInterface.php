<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Collection\DataType\ValueObjectInterface;

/**
 * Cookie
 */
interface CookieInterface extends ValueObjectInterface
{
    /**
     *  Возвращает имя
     */
    public function getName(): ?string;

    /**
     * Устанавливает имя
     *
     * @return $this
     */
    public function setName(?string $name);

    /**
     * Возвращает значение
     */
    public function getValue(): ?string;

    /**
     * Устанавливает значение
     *
     * @return $this
     */
    public function setValue(?string $value);

    /**
     * Возвращает домен
     */
    public function getDomain(): ?string;

    /**
     * Устанавливает домен
     *
     * @return $this
     */
    public function setDomain(?string $domain);

    /**
     * Возвращает путь
     */
    public function getPath(): string;

    /**
     * Устанавливает путь
     *
     * @return $this
     */
    public function setPath(string $path);

    /**
     * Время жизни куки в секундах
     */
    public function getMaxAge(): ?int;

    /**
     * Время жизни куки в секундах
     *
     * @return $this
     */
    public function setMaxAge(?int $maxAge);

    /**
     * UNIX timestamp когда кука истечет
     */
    public function getExpires(): ?int;

    /**
     * UNIX timestamp когда кука истечет
     *
     * @param string|int|null $timestamp
     *
     * @return $this
     */
    public function setExpires($timestamp);

    /**
     * Истекла кука или нет
     */
    public function isExpired(): bool;

    /**
     * Флаг secure
     */
    public function getSecure(): bool;

    /**
     * Флаг secure
     *
     * @return $this
     */
    public function setSecure(bool $secure);

    /**
     * Флаг HttpOnly
     */
    public function getHttpOnly(): bool;

    /**
     * Флаг HttpOnly
     *
     * @return $this
     */
    public function setHttpOnly(bool $httpOnly);

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
     * Валидация куки
     */
    public function validate(): void;

    /**
     * Создать куку из строки
     *
     * @return static
     */
    public static function fromString(string $string);
}
