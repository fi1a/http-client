<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Тело ответа
 */
class ResponseBody extends AbstractBody implements ResponseBodyInterface
{
    /**
     * @var mixed
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $body;

    /**
     * @var string
     */
    private $raw = '';

    /**
     * @inheritDoc
     */
    public function withBody(string $raw, ?string $mime = null): void
    {
        $this->raw = $raw;
        $this->withContentType($mime);
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function getRaw(): string
    {
        return $this->raw;
    }

    /**
     * @inheritDoc
     */
    public function has(): bool
    {
        return $this->raw !== '';
    }

    /**
     * @inheritDoc
     */
    protected function transform(): void
    {
        $this->body = $this->raw;
        $contentType = $this->getContentType();
        if ($contentType) {
            $parser = ContentTypeEncodeRegistry::get($contentType);
            if ($parser) {
                $this->body = $parser->decode($this->raw);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int
    {
        return mb_strlen($this->raw, '8bit');
    }
}
