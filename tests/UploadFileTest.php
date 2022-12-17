<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\FileInterface;
use Fi1a\Filesystem\Filesystem;
use Fi1a\HttpClient\UploadFile;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Загружаемый файл
 */
class UploadFileTest extends TestCase
{
    /**
     * Загружаемый файл
     */
    public function testUploadFile(): void
    {
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/Resources'));
        $uploadFile = new UploadFile('file1', $filesystem->factoryFile('./file1.txt'));
        $this->assertEquals('file1', $uploadFile->getName());
        $this->assertInstanceOf(FileInterface::class, $uploadFile->getFile());
    }

    /**
     * Исключение при пустом имени
     */
    public function testSetNameException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/Resources'));
        new UploadFile('', $filesystem->factoryFile('./file1.txt'));
    }
}
