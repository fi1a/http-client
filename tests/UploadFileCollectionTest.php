<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\Filesystem;
use Fi1a\HttpClient\UploadFile;
use Fi1a\HttpClient\UploadFileCollection;
use Fi1a\HttpClient\UploadFileInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Коллекция загружаемых файлов
 */
class UploadFileCollectionTest extends TestCase
{
    /**
     * Коллекция загружаемых файлов
     */
    public function testCollection(): void
    {
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/Resources'));
        $collection = new UploadFileCollection();
        $collection[] = ['name' => 'file1', 'file' => $filesystem->factoryFile('./file1.txt')];
        $collection[] = new UploadFile('file2', $filesystem->factoryFile('./file2.txt'));
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(UploadFileInterface::class, $collection[0]);
        $this->assertInstanceOf(UploadFileInterface::class, $collection[1]);
    }

    /**
     * Исключение при передаче не массива
     */
    public function testArrayException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new UploadFileCollection();
        $collection[] = 'string';
    }

    /**
     * Исключение при отсутсвии файла
     */
    public function testFileException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new UploadFileCollection();
        $collection[] = ['name' => 'file1',];
    }

    /**
     * Исключение при отсутсвии имени
     */
    public function testNameException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/Resources'));
        $collection = new UploadFileCollection();
        $collection[] = ['file' => $filesystem->factoryFile('./file1.txt')];
    }
}
