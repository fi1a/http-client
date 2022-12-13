<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Collection\DataType\ValueObjectInterface;

/**
 * Конфигурация
 */
interface ConfigInterface extends ValueObjectInterface
{
    /**
     * Требовать проверки SSL-сертификата
     */
    public function getSslVerify(): bool;

    /**
     * Требовать проверки SSL-сертификата
     *
     * @return $this
     */
    public function setSslVerify(bool $sslVerify);

    /**
     * Таймаут соединения
     */
    public function getTimeout(): int;

    /**
     * Таймаут соединения
     *
     * @return $this
     */
    public function setTimeout(int $timeout);

    /**
     * Вернуть сжатие ответа
     */
    public function getCompress(): ?string;

    /**
     * Установить сжатие ответа
     *
     * @return $this
     */
    public function setCompress(?string $compress = null);

    /**
     * Разрешены редиректы или нет
     */
    public function getAllowRedirects(): bool;

    /**
     * Разрешить редиректы
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function setAllowRedirects(bool $allowRedirects = true);

    /**
     * Максимальное число редиректов
     */
    public function getMaxRedirects(): int;

    /**
     * Установить максимальное число редиректов
     *
     * @return $this
     */
    public function setMaxRedirects(int $maxRedirects = 10);
}
