<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Format\Formatter;
use InvalidArgumentException;

/**
 * URI
 */
class Uri implements UriInterface
{
    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $scheme;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $user;

    /**
     * @var string|null
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $password;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $host;

    /**
     * @var int|null
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $port;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $path;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $query;

    /**
     * @var mixed[]
     */
    private $queryParams = [];

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $fragment;

    /**
     * @inheritDoc
     */
    public function __construct(string $uri = '', array $variables = [])
    {
        $parsed = parse_url(Formatter::format($uri, $variables));
        $this->withScheme($parsed['scheme'] ?? 'https')
            ->withUserInfo($parsed['user'] ?? '', $parsed['pass'] ?? null)
            ->withHost($parsed['host'] ?? '')
            ->withPort($parsed['port'] ?? null)
            ->withPath($parsed['path'] ?? '')
            ->withQuery($parsed['query'] ?? '')
            ->withFragment($parsed['fragment'] ?? '');
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function withScheme(string $scheme)
    {
        $scheme = mb_strtolower($scheme);
        if (!in_array($scheme, ['http', 'https'])) {
            throw new InvalidArgumentException(sprintf('Неизвестная схема "%s"', htmlspecialchars($scheme)));
        }

        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        $userInfo = $this->getUser();
        $password = $this->getPassword();
        if (!is_null($password)) {
            $userInfo .= ':' . $password;
        }

        return $userInfo;
    }

    /**
     * @inheritDoc
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo(string $user, ?string $password = null)
    {
        $this->user = $user;
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function withHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function withPort(?int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function withPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function withQuery(string $query)
    {
        $this->query = $query;
        parse_str($query, $this->queryParams);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $queryParams)
    {
        $this->queryParams = $queryParams;
        $this->query = http_build_query($queryParams);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withFragment(string $fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        if (!$this->getHost()) {
            return '';
        }
        $userInfo = $this->getUserInfo();
        $port = $this->getPort();

        return ($userInfo ? $userInfo . '@' : '') . $this->getHost() . ($port ? ':' . $port : '');
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        $authority = $this->getAuthority();
        if (!$authority) {
            return '';
        }

        return $this->getScheme() . '://' . $authority . $this->getPath();
    }

    /**
     * @inheritDoc
     */
    public function getUri(): string
    {
        $url = $this->getUrl();
        if (!$url) {
            return '';
        }
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        return $url . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');
    }

    /**
     * @inheritDoc
     */
    public function getMaskedUri(): string
    {
        if (!$this->getHost()) {
            return '';
        }
        $userInfo = $this->getUserInfo();
        $port = $this->getPort();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        return $this->getScheme() . '://' . ($userInfo ? '######:######@' : '')
            . $this->getHost() . ($port ? ':' . $port : '') . $this->getPath()
            . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');
    }

    /**
     * @inheritDoc
     */
    public function replace(string $uri = '', array $variables = [])
    {
        $parsed = parse_url(Formatter::format($uri, $variables));
        if (isset($parsed['scheme'])) {
            $this->withScheme($parsed['scheme']);
        }
        if (isset($parsed['user']) && isset($parsed['pass'])) {
            $this->withUserInfo($parsed['user'], $parsed['pass']);
        }
        if (isset($parsed['host'])) {
            $this->withHost($parsed['host']);
        }
        if (isset($parsed['port'])) {
            $this->withPort($parsed['port']);
        }
        $this->withPath($parsed['path'] ?? '')
            ->withQuery($parsed['query'] ?? '')
            ->withFragment($parsed['fragment'] ?? '');

        return $this;
    }
}
