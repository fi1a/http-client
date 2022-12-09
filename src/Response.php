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
     * @var mixed
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $body;

    /**
     * @var string
     */
    private $rawBody = '';

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
        if ($statusCode < 0) {
            throw new InvalidArgumentException('Код статуса не может быть меньше 0');
        }
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;

        return $this;
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
    public function withBody(string $rawBody, ?string $mime = null)
    {
        if (!is_null($mime)) {
            $this->withContentType($mime);
        }

        $this->rawBody = $rawBody;
        $this->body = $rawBody;
        $contentType = $this->getContentType();
        if ($contentType) {
            $parser = ParserRegistry::get($contentType);
            if ($parser) {
                $this->body = $parser->parse($rawBody);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasBody(): bool
    {
        return $this->rawBody !== '';
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function getRawBody(): string
    {
        return $this->rawBody;
    }
}
