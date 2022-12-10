<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Тело запроса
 */
class RequestBody extends AbstractBody implements RequestBodyInterface
{
    /**
     * @var string
     */
    private $body = '';

    /**
     * @var mixed
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $raw;

    /**
     * @inheritDoc
     */
    public function withBody($raw, ?string $mime = null): void
    {
        $this->raw = $raw;
        $this->withContentType($mime);
    }

    /**
     * @inheritDoc
     */
    public function get(): string
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int
    {
        return mb_strlen($this->body);
    }

    /**
     * @inheritDoc
     */
    public function has(): bool
    {
        return $this->body !== '';
    }

    /**
     * @inheritDoc
     */
    protected function transform(): void
    {
        $this->body = '';
        if (is_string($this->raw)) {
            $this->body = $this->raw;
        }

        $contentType = $this->getContentType();
        if ($contentType) {
            $parser = ParserRegistry::get($contentType);
            if ($parser) {
                $this->body = $parser->encode($this->raw);
            }
        }
    }
}
