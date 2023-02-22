<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\HttpInterface;
use Fi1a\Http\Mime;
use Fi1a\Http\Uri;
use Fi1a\Http\UriInterface;
use Fi1a\HttpClient\Cookie\Cookie;
use Fi1a\HttpClient\Cookie\CookieInterface;
use Fi1a\HttpClient\Middlewares\MiddlewareCollection;
use Fi1a\HttpClient\Middlewares\MiddlewareCollectionInterface;
use Fi1a\HttpClient\Middlewares\MiddlewareInterface;
use Fi1a\HttpClient\Proxy\ProxyInterface;

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

    /**
     * @var ProxyInterface|null
     */
    private $proxy;

    protected function __construct()
    {
        parent::__construct();
        $this->mutable = true;
        $this->middlewares = new MiddlewareCollection();
        $this->body = new RequestBody();
        $this->withMethod(HttpInterface::GET)
            ->withUri(new Uri());
        $this->mutable = false;
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
        $object = $this->getObject();

        $object->method = mb_strtoupper($method);

        return $object;
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
        $object = $this->getObject();

        $object = $object->withMethod(HttpInterface::GET)
            ->withUri($this->createUri($uri));

        $object->body = $object->body->setContentType($mime);

        return $object;
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
    public function post($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null)
    {
        $object = $this->getObject();

        if (!$mime) {
            $mime = 'form';
        }

        return $object->withMethod(HttpInterface::POST)
            ->withUri($this->createUri($uri))
            ->withBody($body, $mime, $files);
    }

    /**
     * @inheritDoc
     */
    public function put($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null)
    {
        $object = $this->getObject();

        if (!$mime) {
            $mime = 'form';
        }

        return $object->withMethod(HttpInterface::PUT)
            ->withUri($this->createUri($uri))
            ->withBody($body, $mime, $files);
    }

    /**
     * @inheritDoc
     */
    public function patch($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null)
    {
        return $this->getObject()->withMethod(HttpInterface::PATCH)
            ->withUri($this->createUri($uri))
            ->withBody($body, $mime, $files);
    }

    /**
     * @inheritDoc
     */
    public function delete($uri, ?string $mime = null)
    {
        $object = $this->getObject();

        $object = $object->withMethod(HttpInterface::DELETE)
            ->withUri($this->createUri($uri));

        $object->body = $object->body->setContentType($mime);

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function head($uri)
    {
        return $this->getObject()->withMethod(HttpInterface::HEAD)
            ->withUri($this->createUri($uri));
    }

    /**
     * @inheritDoc
     */
    public function options($uri)
    {
        return $this->getObject()->withMethod(HttpInterface::OPTIONS)
            ->withUri($this->createUri($uri));
    }

    /**
     * @inheritDoc
     */
    public function withMime(?string $mime = null)
    {
        $object = $this->getObject();

        $object->body = $object->body->setContentType($mime);

        return $object->withExpectedType($mime);
    }

    /**
     * @inheritDoc
     */
    public function withExpectedType(?string $mime = null)
    {
        $object = $this->getObject();

        $object->expectedType = $mime ? Mime::getMime($mime) : null;

        return $object;
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
    public function withBody($body, ?string $mime = null, ?UploadFileCollectionInterface $files = null)
    {
        $object = $this->getObject();

        $object->body->setBody($body, $mime, $files);

        return $object;
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
        $object = $this->getObject();

        if (!$uri instanceof EncodedUriInterface) {
            $uri = EncodedUri::create($uri);
        }
        $object->uri = $uri;
        foreach ($this->getCookies() as $cookie) {
            assert($cookie instanceof CookieInterface);
            $cookie->setDomain($uri->host());
        }

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function withMiddleware(MiddlewareInterface $middleware, ?int $sort = null)
    {
        $object = $this->getObject();

        if (!is_null($sort)) {
            $middleware->setSort($sort);
        }
        $object->middlewares[] = $middleware;

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): MiddlewareCollectionInterface
    {
        return $this->middlewares;
    }

    /**
     * @inheritDoc
     */
    public function withProxy(?ProxyInterface $proxy)
    {
        $object = $this->getObject();

        $object->proxy = $proxy;

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function getProxy(): ?ProxyInterface
    {
        return $this->proxy;
    }

    /**
     * @inheritDoc
     */
    public function addCookie(string $name, string $value)
    {
        $cookie = new Cookie();
        $cookie->setName($name)
            ->setValue($value)
            ->setDomain($this->getUri()->host());

        $this->getCookies()->add($cookie);

        return $cookie;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        parent::__clone();
        $this->uri = clone $this->uri;
        $this->body = clone $this->body;
        $this->middlewares = clone $this->middlewares;
        if ($this->proxy) {
            $this->proxy = clone $this->proxy;
        }
    }
}
