<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use InvalidArgumentException;

/**
 * Объект ответа
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @var int
     */
    private $statusCode = 0;

    /**
     * @var string|null
     */
    private $reasonPhrase;

    /**
     * @var ResponseBodyInterface
     */
    private $body;

    public function __construct()
    {
        parent::__construct();
        $this->body = new ResponseBody();
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): ?string
    {
        return $this->reasonPhrase;
    }

    /**
     * @inheritDoc
     */
    public function withStatus(int $statusCode, string $reasonPhrase = '')
    {
        $object = $this->getObject();

        if ($statusCode < 0) {
            throw new InvalidArgumentException('Код статуса не может быть меньше 0');
        }
        $object->statusCode = $statusCode;
        $object->reasonPhrase = $reasonPhrase;

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function hasErrors(): bool
    {
        return $this->statusCode >= 400;
    }

    /**
     * @inheritDoc
     */
    public function isSuccess(): bool
    {
        return !$this->hasErrors() && $this->statusCode !== 0;
    }

    /**
     * @inheritDoc
     */
    public function withBody(string $rawBody, ?string $mime = null)
    {
        $object = $this->getObject();

        $object->body->setBody($rawBody, $mime);

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): ResponseBodyInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        parent::__clone();
        $this->body = clone $this->body;
    }
}
