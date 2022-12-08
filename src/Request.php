<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Объект запроса
 */
class Request implements RequestInterface
{
    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $method;

    /**
     * @var UriInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $uri;

    /**
     * @var string|null
     */
    private $contentType;

    /**
     * @var string|null
     */
    private $expectedType;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var MimeInterface
     */
    private $mime;

    protected function __construct()
    {
        $this->mime = new Mime();
        $this->withMethod(HttpInterface::GET)
            ->withUri(new Uri());
    }

    /**
     * @inheritDoc
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function withMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function get($uri, ?string $mime = null)
    {
        $this->withMethod(HttpInterface::GET)
            ->withUri($this->createUri($uri))
            ->withMime($mime);

        return $this;
    }

    /**
     * Создает объект Uri
     *
     * @param string|UriInterface $uri
     */
    private function createUri($uri): UriInterface
    {
        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }

        return $uri;
    }

    /**
     * @inheritDoc
     */
    public function post($uri, $payload = null, ?string $mime = null)
    {
        $this->withMethod(HttpInterface::POST)
            ->withUri($this->createUri($uri))
            ->withBody($payload, $mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function put($uri, $payload = null, ?string $mime = null)
    {
        $this->withMethod(HttpInterface::PUT)
            ->withUri($this->createUri($uri))
            ->withBody($payload, $mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function patch($uri, $payload = null, ?string $mime = null)
    {
        $this->withMethod(HttpInterface::PATCH)
            ->withUri($this->createUri($uri))
            ->withBody($payload, $mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete($uri, ?string $mime = null)
    {
        $this->withMethod(HttpInterface::DELETE)
            ->withUri($this->createUri($uri))
            ->withMime($mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function head($uri)
    {
        $this->withMethod(HttpInterface::HEAD)
            ->withUri($this->createUri($uri));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function options($uri)
    {
        $this->withMethod(HttpInterface::OPTIONS)
            ->withUri($this->createUri($uri));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withMime(?string $mime = null)
    {
        return $this->withContentType($mime)
            ->withExpectedType($mime);
    }

    /**
     * @inheritDoc
     */
    public function withContentType(?string $mime = null)
    {
        $this->contentType = $mime ? $this->mime->getMime($mime) : null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * @inheritDoc
     */
    public function withExpectedType(?string $mime = null)
    {
        $this->expectedType = $mime ? $this->mime->getMime($mime) : null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExpectedType(): ?string
    {
        return $this->expectedType;
    }

    /**
     * @inheritDoc
     */
    public function withBody($payload, ?string $mime = null)
    {
        $this->payload = $payload;
        $this->withMime($mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri)
    {
        $this->uri = $uri;

        return $this;
    }
}
