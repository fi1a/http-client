<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Collection\AbstractInstanceCollection;

/**
 * Коллекция заголовков
 */
class HeaderCollection extends AbstractInstanceCollection implements HeaderCollectionInterface
{
    /**
     * @inheritDoc
     */
    protected function factory($key, $value)
    {
        [$name, $headerValue] = (array) $value;

        return new Header((string) $name, $headerValue ? (string) $headerValue : null);
    }

    /**
     * @inheritDoc
     */
    protected function isInstance($value): bool
    {
        return $value instanceof HeaderInterface;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        /**
         * @var HeaderInterface $header
         */
        foreach ($this as $header) {
            if (mb_strtolower($header->getName()) === mb_strtolower($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): HeaderCollectionInterface
    {
        return $this->filter(function (HeaderInterface $header) use ($name) {
            return mb_strtolower($header->getName()) === mb_strtolower($name);
        });
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): bool
    {
        $headers = $this->getArrayCopy();
        $return = false;

        /**
         * @var HeaderInterface $header
         */
        foreach ($headers as $index => $header) {
            if (mb_strtolower($header->getName()) === mb_strtolower($name)) {
                unset($headers[$index]);
                $return = true;
            }
        }

        $this->exchangeArray($headers);
        $this->resetKeys();

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function getFirstHeader(string $name): ?HeaderInterface
    {
        /**
         * @var HeaderInterface[] $headers
         */
        $headers = $this->getArrayCopy();

        return $this->getFirstHeaderInternal($name, $headers);
    }

    /**
     * @inheritDoc
     */
    public function getLastHeader(string $name): ?HeaderInterface
    {
        /**
         * @var HeaderInterface[] $headers
         */
        $headers = array_reverse($this->getArrayCopy());

        return $this->getFirstHeaderInternal($name, $headers);
    }

    /**
     * Возвращает первый найденный заголовок с определенным именем
     *
     * @param HeaderInterface[]  $headers
     */
    private function getFirstHeaderInternal(string $name, array $headers): ?HeaderInterface
    {
        foreach ($headers as $header) {
            if (mb_strtolower($header->getName()) === mb_strtolower($name)) {
                return $header;
            }
        }

        return null;
    }
}
