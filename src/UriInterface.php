<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * URI
 */
interface UriInterface
{
    /**
     * @param mixed[] $variables
     */
    public function __construct(string $uri = '', array $variables = []);

    /**
     * Схема
     */
    public function getScheme(): string;

    /**
     * Задать схему
     *
     * @return $this
     */
    public function withScheme(string $scheme);

    /**
     * Компонент информации о пользователе URI
     */
    public function getUserInfo(): string;

    /**
     * Возвращает имя пользователя
     */
    public function getUser(): string;

    /**
     * Возвращает пароль
     */
    public function getPassword(): ?string;

    /**
     * Задать информацию о пользователе
     *
     * @return $this
     */
    public function withUserInfo(string $user, ?string $password = null);

    /**
     * Хост
     */
    public function getHost(): string;

    /**
     * Задать хост
     *
     * @return $this
     */
    public function withHost(string $host);

    /**
     * Порт
     */
    public function getPort(): ?int;

    /**
     * Задать порт
     *
     * @return $this
     */
    public function withPort(?int $port);

    /**
     * Часть пути URI
     */
    public function getPath(): string;

    /**
     * Установить часть пути URI
     *
     * @return $this
     */
    public function withPath(string $path);

    /**
     * Строка запроса в URI
     */
    public function getQuery(): string;

    /**
     * Задать строку запроса URI
     *
     * @return $this
     */
    public function withQuery(string $query);

    /**
     * Массив запроса в URI
     *
     * @return mixed[]
     */
    public function getQueryParams(): array;

    /**
     * Задать массив запроса в URI
     *
     * @param mixed[] $queryParams
     *
     * @return $this
     */
    public function withQueryParams(array $queryParams);

    /**
     * Фрагмент URI
     */
    public function getFragment(): string;

    /**
     * Задать фрагмент URI
     *
     * @return $this
     */
    public function withFragment(string $fragment);

    /**
     * Возвращает URL
     */
    public function getUrl(): string;

    /**
     * Возвращает URI
     */
    public function getUri(): string;

    /**
     * Компонент полномочий URI
     */
    public function getAuthority(): string;

    /**
     * Возвращает URI с маской на данных авторизации
     */
    public function getMaskedUri(): string;

    /**
     * Заменить адрес переданным значением
     *
     * @param mixed[]  $variables
     *
     * @return $this
     */
    public function replace(string $uri = '', array $variables = []);
}
