<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Collection\DataType\ValueObject;
use InvalidArgumentException;

/**
 * Конфигурация
 */
class Config extends ValueObject implements ConfigInterface
{
    /**
     * @inheritDoc
     */
    protected function getDefaultModelValues()
    {
        return [
            'ssl_verify' => true,
            'timeout'  => 10,
            'compress' => null,
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

    /**
     * @inheritDoc
     */
    public function getCompress(): ?string
    {
        /**
         * @var string|null $compress
         */
        $compress = $this->modelGet('compress');

        return $compress;
    }

    /**
     * @inheritDoc
     */
    public function setCompress(?string $compress = null)
    {
        if (!is_null($compress)) {
            $compress = mb_strtolower($compress);
        }
        if (!is_null($compress) && !in_array($compress, ['gzip'])) {
            throw new InvalidArgumentException(sprintf('Не поддерживаемый тип сжатия "%s"', $compress));
        }

        $this->modelSet('compress', $compress);

        return $this;
    }
}
