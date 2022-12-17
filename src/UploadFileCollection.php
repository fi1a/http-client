<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Collection\AbstractInstanceCollection;
use Fi1a\Filesystem\FileInterface;
use InvalidArgumentException;

/**
 * Коллекция загружаемых файлов
 */
class UploadFileCollection extends AbstractInstanceCollection implements UploadFileCollectionInterface
{
    /**
     * @inheritDoc
     */
    protected function factory($key, $value)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Ожидается массив для создания загружаемого файла');
        }
        if (!isset($value['name']) || !is_string($value['name']) || !$value['name']) {
            throw new InvalidArgumentException('Не передано имя загружаемого файла');
        }
        if (!isset($value['file']) || !($value['file'] instanceof FileInterface)) {
            throw new InvalidArgumentException('Не передан файл для загружаемого файла');
        }

        return new UploadFile($value['name'], $value['file']);
    }

    /**
     * @inheritDoc
     */
    protected function isInstance($value): bool
    {
        return $value instanceof UploadFileInterface;
    }
}
