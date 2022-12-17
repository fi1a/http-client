<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\ContentTypeEncodes;

use Fi1a\HttpClient\ContentTypeEncodes\JsonContentTypeEncode;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\Unit\HttpClient\TestCase\UploadFileTestCase;

/**
 * Парсер json типа контента
 */
class JsonContentTypeEncodeTest extends UploadFileTestCase
{
    /**
     * Декодирование
     */
    public function testDecode(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);
        $encode = new JsonContentTypeEncode();
        $this->assertEquals($array, $encode->decode($json));
    }

    /**
     * Кодирование
     */
    public function testEncode(): void
    {
        $files = $this->getUploadFiles();
        $array = ['foo' => 'bar'];
        $json = json_encode($array + [
            'file1' => realpath(__DIR__ . '/../Resources/file1.txt'),
            'file2' => realpath(__DIR__ . '/../Resources/file2.txt'),
        ]);
        $encode = new JsonContentTypeEncode();
        $this->assertEquals($json, $encode->encode($array, $files));
    }

    /**
     * Заголовок
     */
    public function testGetContentTypeHeader(): void
    {
        $encode = new JsonContentTypeEncode();
        $this->assertEquals(MimeInterface::JSON, $encode->getContentTypeHeader());
    }
}
