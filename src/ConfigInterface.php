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
}
