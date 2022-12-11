<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Collection\DataType\ValueObject;

/**
 * Конфигурация
 */
class Config extends ValueObject implements ConfigInterface
{
    /**
     * Возвращает массив со значениями по умолчанию
     *
     * @return mixed[]
     */
    protected function getDefaultModelValues()
    {
        return [
            'ssl_verify' => true,
            'timeout'  => 10,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSslVerify(): bool
    {
        return (bool) $this->modelGet('ssl_verify');
    }

    /**
     * @inheritDoc
     */
    public function setSslVerify(bool $sslVerify)
    {
        $this->modelSet('ssl_verify', $sslVerify);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTimeout(): int
    {
        return (int) $this->modelGet('timeout');
    }

    /**
     * @inheritDoc
     */
    public function setTimeout(int $timeout)
    {
        $this->modelSet('timeout', $timeout);

        return $this;
    }
}
