<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Filesystem\FileInterface;
use Fi1a\Http\Mime;
use Fi1a\Http\MimeInterface;
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
    public function setBody($raw, ?string $mime = null, ?UploadFileCollectionInterface $files = null)
    {
        $this->raw = $raw;
        if (is_null($files)) {
            $files = new UploadFileCollection();
        }
        $this->uploadFiles = $files;
        if ($files->count()) {
            $mime = MimeInterface::UPLOAD;
        }
        $this->setContentType($mime);

        return $this;
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
        return mb_strlen($this->body, '8bit');
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
    public function setUploadFiles(?UploadFileCollectionInterface $files)
    {
        if (is_null($files)) {
            $files = new UploadFileCollection();
        }
        $this->uploadFiles = $files;
        if ($files->count()) {
            $this->setContentType(MimeInterface::UPLOAD);
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
    public function setContentType(?string $mime = null)
    {
        $this->encode = false;
        if ($mime) {
            $this->encode = ContentTypeEncodeRegistry::get($mime);
        }

        $this->contentType = $mime ? Mime::getMime($mime) : null;
        $this->transform();

        return $this;
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

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->uploadFiles = clone $this->uploadFiles;
    }
}
