<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Proxy;

use InvalidArgumentException;

/**
 * Абстрактный класс прокси
 */
abstract class AbstractProxy implements ProxyInterface
{
    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $host;

    /**
     * @var int
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $port;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    public function __construct(string $host, int $port, ?string $username = null, ?string $password = null)
    {
        $this->setHost($host)
            ->setPort($port)
            ->setUserName($username)
            ->setPassword($password);
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
    public function setHost(string $host)
    {
        if (!$host) {
            throw new InvalidArgumentException('Хост прокси не может быть пустым');
        }
        $this->host = $host;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function setPort(int $port)
    {
        if (!$port) {
            throw new InvalidArgumentException('Порт прокси не может быть меньше или равен 0');
        }
        $this->port = $port;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserName(): ?string
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function setUserName(?string $username)
    {
        $this->username = $username;

        return $this;
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
    public function setPassword(?string $password)
    {
        $this->password = $password;

        return $this;
    }
}
