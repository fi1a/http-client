<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

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
     * @var string|null
     */
    private $contentType;

    public function __construct()
    {
        $this->headers = new HeaderCollection();
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
    public function withContentType(?string $mime = null)
    {
        $this->contentType = $mime ? Mime::getMime($mime) : null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }
}
