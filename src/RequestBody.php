<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Filesystem\FileInterface;
use Fi1a\HttpClient\ContentTypeEncodes\ContentTypeEncodeInterface;

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
     * @var UploadFileCollectionInterface
     */
    private $uploadFiles;

    /**
     * @var ContentTypeEncodeInterface|false
     */
    private $encode = false;

    public function __construct()
    {
        $this->uploadFiles = new UploadFileCollection();
    }

    /**
     * @inheritDoc
     */
    public function withBody($raw, ?string $mime = null, ?UploadFileCollectionInterface $files = null): void
    {
        $this->raw = $raw;
        if (is_null($files)) {
            $files = new UploadFileCollection();
        }
        $this->uploadFiles = $files;
        if ($files->count()) {
            $mime = MimeInterface::UPLOAD;
        }
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
    public function withUploadFiles(?UploadFileCollectionInterface $files)
    {
        if (is_null($files)) {
            $files = new UploadFileCollection();
        }
        $this->uploadFiles = $files;
        if ($files->count()) {
            $this->withContentType(MimeInterface::UPLOAD);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addUploadFile(string $name, FileInterface $file)
    {
        $this->uploadFiles[] = new UploadFile($name, $file);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUploadFiles(): UploadFileCollectionInterface
    {
        return $this->uploadFiles;
    }

    /**
     * @inheritDoc
     */
    public function getContentTypeHeader(): ?string
    {
        if ($this->encode) {
            return $this->encode->getContentTypeHeader();
        }

        return $this->getContentType();
    }

    /**
     * @inheritDoc
     */
    public function withContentType(?string $mime = null)
    {
        $this->encode = false;
        if ($mime) {
            $this->encode = ContentTypeEncodeRegistry::get($mime);
        }

        return parent::withContentType($mime);
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

        if ($this->encode) {
            $this->body = $this->encode->encode($this->raw, $this->uploadFiles);
        }
    }
}
