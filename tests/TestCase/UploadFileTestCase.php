<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\TestCase;

use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\Filesystem;
use Fi1a\HttpClient\UploadFileCollection;
use Fi1a\HttpClient\UploadFileCollectionInterface;
use PHPUnit\Framework\TestCase;

/**
 * Для тестов с загружаемыми файлами
 */
class UploadFileTestCase extends TestCase
{
    /**
     * Возвращает загружаемые файлы
     */
    protected function getUploadFiles(): UploadFileCollectionInterface
    {
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/../Resources'));
        $files = new UploadFileCollection();
        $files[] = [
            'name' => 'file1',
            'file' => $filesystem->factoryFile('./file1.txt'),
        ];
        $files[] = [
            'name' => 'file2',
            'file' => $filesystem->factoryFile('./file2.txt'),
        ];
        $files[] = [
            'name' => 'not-exists',
            'file' => $filesystem->factoryFile('./not-exists.txt'),
        ];

        return $files;
    }
}
