<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\ContentTypeEncodes;

use Fi1a\HttpClient\ContentTypeEncodes\FormContentTypeEncode;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\UploadFileCollection;
use Fi1a\Unit\HttpClient\TestCase\UploadFileTestCase;
use InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

/**
 * Парсер application/x-www-form-urlencoded типа контента
 */
class FormContentTypeEncodeTest extends UploadFileTestCase
{
    /**
     * Декодирование
     */
    public function testDecode(): void
    {
        $this->expectException(LogicException::class);
        $encode = new FormContentTypeEncode();
        $encode->decode('');
    }

    /**
     * Кодирование
     */
    public function testEncode(): void
    {
        $files = $this->getUploadFiles();
        $array = ['foo' => 'bar'];
        $query = http_build_query(
            $array + [
                'file1' => realpath(__DIR__ . '/../Resources/file1.txt'),
                'file2' => realpath(__DIR__ . '/../Resources/file2.txt'),
            ],
            '',
            '&'
        );
        $encode = new FormContentTypeEncode();
        $this->assertEquals($query, $encode->encode($array, $files));
    }

    /**
     * Кодирование строки
     */
    public function testEncodeString(): void
    {
        $files = $this->getUploadFiles();
        $array = ['foo' => 'bar'];
        $query = http_build_query($array, '', '&');
        $encode = new FormContentTypeEncode();
        $this->assertEquals($query, $encode->encode($query, $files));
    }

    /**
     * Исключение при не поддерживаемом типе
     */
    public function testEncodeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $files = $this->getUploadFiles();
        $encode = new FormContentTypeEncode();
        $encode->encode($this, $files);
    }

    /**
     * Заголовок
     */
    public function testGetContentTypeHeader(): void
    {
        $encode = new FormContentTypeEncode();
        $this->assertEquals(MimeInterface::FORM, $encode->getContentTypeHeader());
    }

    /**
     * Пустой массив для кодирования
     */
    public function testEncodeEmptyArray(): void
    {
        $array = [];
        $query = http_build_query($array, '', '&');
        $encode = new FormContentTypeEncode();
        $this->assertEquals($query, $encode->encode($array, new UploadFileCollection()));
    }
}
