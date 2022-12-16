<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\HttpClient\Middlewares\MiddlewareCollection;
use Fi1a\HttpClient\Middlewares\MiddlewareCollectionInterface;
use Fi1a\HttpClient\Middlewares\MiddlewareInterface;

/**
 * Объект запроса
 */
class Request extends Message implements RequestInterface
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
    private $expectedType;

    /**
     * @var RequestBodyInterface
     */
    private $body;

    /**
     * @var MiddlewareCollectionInterface
     */
    private $middlewares;

    protected function __construct()
    {
        parent::__construct();
        $this->middlewares = new MiddlewareCollection();
        $this->body = new RequestBody();
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
        $this->method = mb_strtoupper($method);

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
            ->withUri($this->createUri($uri));

        $this->body->withContentType($mime);

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
    public function post($uri, $body = null, ?string $mime = null)
    {
        if (!$mime) {
            $mime = 'form';
        }

        $this->withMethod(HttpInterface::POST)
            ->withUri($this->createUri($uri))
            ->withBody($body, $mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function put($uri, $body = null, ?string $mime = null)
    {
        if (!$mime) {
            $mime = 'form';
        }

        $this->withMethod(HttpInterface::PUT)
            ->withUri($this->createUri($uri))
            ->withBody($body, $mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function patch($uri, $body = null, ?string $mime = null)
    {
        $this->withMethod(HttpInterface::PATCH)
            ->withUri($this->createUri($uri))
            ->withBody($body, $mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete($uri, ?string $mime = null)
    {
        $this->withMethod(HttpInterface::DELETE)
            ->withUri($this->createUri($uri));

        $this->body->withContentType($mime);

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
        $this->body->withContentType($mime);
        $this->withExpectedType($mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withExpectedType(?string $mime = null)
    {
        $this->expectedType = $mime ? Mime::getMime($mime) : null;

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
    public function withBody($body, ?string $mime = null)
    {
        $this->body->withBody($body, $mime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): RequestBodyInterface
    {
        return $this->body;
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

    /**
     * @inheritDoc
     */
    public function withMiddleware(MiddlewareInterface $middleware, ?int $sort = null)
    {
        if (!is_null($sort)) {
            $middleware->setSort($sort);
        }
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): MiddlewareCollectionInterface
    {
        return $this->middlewares;
    }
}
