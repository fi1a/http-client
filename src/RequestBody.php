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
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

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
            $parser = ContentTypeEncodeRegistry::get($contentType);
            if ($parser) {
                $this->body = $parser->encode($this->raw);
            }
        }

        $this->request->withoutHeader('Content-Type');
        $this->request->withoutHeader('Content-Length');
        if (!$contentType) {
            $contentType = MimeInterface::HTML;
            if (
                $this->request->getMethod() === HttpInterface::POST
                || $this->request->getMethod() === HttpInterface::PUT
            ) {
                $contentType = MimeInterface::FORM;
            }
        }
        $this->request->withHeader('Content-Type', $contentType);
        if ($this->getSize()) {
            $this->request->withHeader('Content-Length', (string) $this->getSize());
        }
    }
}
