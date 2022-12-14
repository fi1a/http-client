<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Cookie\CookieCollection;
use Fi1a\HttpClient\Cookie\CookieCollectionInterface;

/**
 * Сообщение
 */
class Message implements MessageInterface
{
    /**
     * @var string
     */
    private $protocol = '1.1';

    /**
     * @var HeaderCollectionInterface
     */
    private $headers;

    /**
     * @var string
     */
    private $encoding = 'utf-8';

    /**
     * @var CookieCollectionInterface
     */
    private $cookies;

    public function __construct()
    {
        $this->headers = new HeaderCollection();
        $this->cookies = new CookieCollection();
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion(string $version)
    {
        $this->protocol = $version;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @inheritDoc
     */
    public function withEncoding(string $encoding)
    {
        $this->encoding = mb_strtolower($encoding);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): HeaderCollectionInterface
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return $this->headers->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): HeaderCollectionInterface
    {
        return $this->headers->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getFirstHeader(string $name): ?HeaderInterface
    {
        return $this->headers->getFirstHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getLastHeader(string $name): ?HeaderInterface
    {
        return $this->headers->getLastHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function withHeader(string $name, string $value)
    {
        $this->withAddedHeader($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader(string $name, string $value): HeaderInterface
    {
        $header = new Header($name, $value);
        $this->headers[] = $header;

        return $header;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): bool
    {
        return $this->headers->withoutHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function clearHeaders(): bool
    {
        $this->headers->exchangeArray([]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCookies(): CookieCollectionInterface
    {
        return $this->cookies;
    }

    /**
     * @inheritDoc
     */
    public function withCookies(CookieCollectionInterface $collection)
    {
        $this->cookies = $collection;

        return $this;
    }
}
