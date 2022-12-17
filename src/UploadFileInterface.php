<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Filesystem\FileInterface;

/**
 * Загружаемый файл
 */
interface UploadFileInterface
{
    public function __construct(string $name, FileInterface $file);

    /**
     * Возвращает название
     */
    public function getName(): string;

    /**
     * Устанавливает название
     *
     * @return $this
     */
    public function setName(string $name);

    /**
     * Возвращает загружаемый файл
     */
    public function getFile(): FileInterface;

    /**
     * Устанавливает загружаемый файл
     *
     * @return $this
     */
    public function setFile(FileInterface $file);
}
