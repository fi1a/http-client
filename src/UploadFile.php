<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Filesystem\FileInterface;

/**
 * Загружаемый файл
 */
class UploadFile implements UploadFileInterface
{
    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $name;

    /**
     * @var FileInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $file;

    public function __construct(string $name, FileInterface $file)
    {
        $this->setName($name)
            ->setFile($file);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name)
    {
        if (!$name) {
            throw new \InvalidArgumentException('Имя загружаемого файла не может быть пустым');
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFile(): FileInterface
    {
        return $this->file;
    }

    /**
     * @inheritDoc
     */
    public function setFile(FileInterface $file)
    {
        $this->file = $file;

        return $this;
    }
}
