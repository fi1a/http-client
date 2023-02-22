<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\Header;
use Fi1a\Http\HeaderCollection;
use Fi1a\Http\HeaderCollectionInterface;
use Fi1a\Http\HeaderInterface;
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

    /**
     * @var bool
     */
    protected $mutable = true;

    public function __construct()
    {
        $this->headers = new HeaderCollection();
        $this->cookies = new CookieCollection();
        $this->mutable = false;
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
        $object = $this->getObject();

        $object->protocol = $version;

        return $object;
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
        $object = $this->getObject();

        $object->encoding = mb_strtolower($encoding);

        return $object;
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
    public function withHeaders(HeaderCollectionInterface $headers)
    {
        $object = $this->getObject();

        $object->headers = $headers;

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function addHeader(HeaderInterface $header)
    {
        $this->headers->add($header);

        return $this;
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
        $object = $this->getObject();

        $header = new Header($name, $value);
        $object->headers[] = $header;

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name)
    {
        $object = $this->getObject();

        $object->headers->withoutHeader($name);

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function clearHeaders()
    {
        $object = $this->getObject();

        $object->headers->exchangeArray([]);

        return $object;
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
        $object = $this->getObject();

        $object->cookies = $collection;

        return $object;
    }

    /**
     * Возвращает объет для установки значений
     *
     * @return $this
     */
    protected function getObject()
    {
        return $this->mutable ? $this : clone $this;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->cookies = clone $this->cookies;
    }
}
