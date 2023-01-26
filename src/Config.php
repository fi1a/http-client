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
    protected $modelKeys = ['sslVerify', 'timeout', 'compress', 'allowRedirects', 'maxRedirects', 'cookie'];

    /**
     * @inheritDoc
     */
    protected function getDefaultModelValues()
    {
        return [
            'sslVerify' => true,
            'timeout'  => 10,
            'compress' => null,
            'allowRedirects' => true,
            'maxRedirects' => 10,
            'cookie' => false,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSslVerify(): bool
    {
        return (bool) $this->modelGet('sslVerify');
    }

    /**
     * @inheritDoc
     */
    public function setSslVerify(bool $sslVerify)
    {
        $this->modelSet('sslVerify', $sslVerify);

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

    /**
     * @inheritDoc
     */
    public function getAllowRedirects(): bool
    {
        return (bool) $this->modelGet('allowRedirects');
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function setAllowRedirects(bool $allowRedirects = true)
    {
        $this->modelSet('allowRedirects', $allowRedirects);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMaxRedirects(): int
    {
        return (int) $this->modelGet('maxRedirects');
    }

    /**
     * @inheritDoc
     */
    public function setMaxRedirects(int $maxRedirects = 10)
    {
        if ($maxRedirects < 0) {
            throw new InvalidArgumentException('Значение не может быть меньше 0');
        }

        $this->modelSet('maxRedirects', $maxRedirects);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCookie(bool $cookie)
    {
        $this->modelSet('cookie', $cookie);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCookie(): bool
    {
        return (bool) $this->modelGet('cookie');
    }
}
