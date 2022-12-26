<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\ContentTypeEncodes;

use ErrorException;
use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\ContentTypeEncodes\MultipartFormDataContentTypeEncode;
use Fi1a\HttpClient\UploadFileCollection;
use Fi1a\Unit\HttpClient\TestCase\UploadFileTestCase;
use InvalidArgumentException;
use LogicException;

/**
 * Парсер multipart/form-data типа контента
 */
class MultipartFormDataContentTypeEncodeTest extends UploadFileTestCase
{
    /**
     * Декодирование
     */
    public function testDecode(): void
    {
        $this->expectException(LogicException::class);
        $encode = new MultipartFormDataContentTypeEncode();
        $encode->decode('');
    }

    /**
     * Кодирование
     */
    public function testEncode(): void
    {
        $files = $this->getUploadFiles();
        $array = ['foo' => 'bar'];
        $encode = new MultipartFormDataContentTypeEncode();
        $content = $encode->encode($array, $files);
        $this->assertIsString($content);
        $this->assertEquals(431, mb_strlen($content));
    }

    /**
     * Кодирование строки
     */
    public function testEncodeString(): void
    {
        $files = $this->getUploadFiles();
        $query = 'test';
        $encode = new MultipartFormDataContentTypeEncode();
        $this->assertEquals($query, $encode->encode($query, $files));
    }

    /**
     * Исключение при не поддерживаемом типе
     */
    public function testEncodeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $files = $this->getUploadFiles();
        $encode = new MultipartFormDataContentTypeEncode();
        $encode->encode($this, $files);
    }

    /**
     * Заголовок
     */
    public function testGetContentTypeHeader(): void
    {
        $encode = new MultipartFormDataContentTypeEncode();
        $this->assertStringContainsString(MimeInterface::UPLOAD, $encode->getContentTypeHeader());
    }

    /**
     * Кодирование
     */
    public function testEncodeReadFileException(): void
    {
        $this->expectException(ErrorException::class);
        chmod(__DIR__ . '/../Resources/file1.txt', 0000);
        $files = $this->getUploadFiles();
        $encode = new MultipartFormDataContentTypeEncode();
        try {
            $encode->encode([], $files);
        } catch (ErrorException $exception) {
            chmod(__DIR__ . '/../Resources/file1.txt', 0775);

            throw $exception;
        }
    }

    /**
     * Пустой массив для кодирования
     */
    public function testEncodeEmptyArray(): void
    {
        $array = [];
        $encode = new MultipartFormDataContentTypeEncode();
        $content = $encode->encode($array, new UploadFileCollection());
        $this->assertIsString($content);
        $this->assertEquals(38, mb_strlen($content));
    }
}
