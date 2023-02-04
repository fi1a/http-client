<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Proxy;

/**
 * Прокси
 */
interface ProxyInterface
{
    /**
     * Хост
     */
    public function getHost(): string;

    /**
     * Хост
     *
     * @return $this
     */
    public function setHost(string $host);

    /**
     * Порт
     */
    public function getPort(): int;

    /**
     * Порт
     *
     * @return $this
     */
    public function setPort(int $port);

    /**
     * Пользователь для авторизации
     */
    public function getUserName(): ?string;

    /**
     * Пользователь для авторизации
     *
     * @return $this
     */
    public function setUserName(?string $username);

    /**
     * Пароль для авторизации
     */
    public function getPassword(): ?string;

    /**
     * Пароль для авторизации
     *
     * @return $this
     */
    public function setPassword(?string $password);
}
