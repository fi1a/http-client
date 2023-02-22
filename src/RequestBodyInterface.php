<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Filesystem\FileInterface;

/**
 * Тело запроса
 */
interface RequestBodyInterface extends BodyInterface
{
    /**
     * Установить тело запроса
     *
     * @param mixed $raw
     *
     * @return $this
     */
    public function setBody($raw, ?string $mime = null, ?UploadFileCollectionInterface $files = null);

    /**
     * Возвращает тело запроса
     */
    public function get(): string;

    /**
     * Возвращает тело запроса без примененного преобразования
     *
     * @return mixed
     */
    public function getRaw();

    /**
     * Есть тело запроса или нет
     */
    public function has(): bool;

    /**
     * Прикрепить файлы к телу запроса
     *
     * @return $this
     */
    public function setUploadFiles(?UploadFileCollectionInterface $files);

    /**
     * Добавить загружаемый файл
     *
     * @return $this
     */
    public function addUploadFile(string $name, FileInterface $file);

    /**
     * Возвращает прикрепленные файлы
     */
    public function getUploadFiles(): UploadFileCollectionInterface;

    /**
     * Content type для заголовков
     */
    public function getContentTypeHeader(): ?string;
}
