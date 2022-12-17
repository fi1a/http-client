<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Тело запроса/ответа
 */
abstract class AbstractBody implements BodyInterface
{
    /**
     * @var string|null
     */
    protected $contentType;

    /**
     * Осуществляет парсинг
     */
    abstract protected function transform(): void;

    /**
     * @inheritDoc
     */
    public function withContentType(?string $mime = null)
    {
        $this->contentType = $mime ? Mime::getMime($mime) : null;
        $this->transform();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }
}
