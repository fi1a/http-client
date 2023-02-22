<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\Filesystem;
use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\RequestBody;
use Fi1a\HttpClient\RequestBodyInterface;
use Fi1a\HttpClient\UploadFileCollectionInterface;
use Fi1a\HttpClient\UploadFileInterface;
use Fi1a\Unit\HttpClient\TestCase\UploadFileTestCase;

/**
 * Тело запроса
 */
class RequestBodyTest extends UploadFileTestCase
{
    /**
     * Возвращает тело запроса
     */
    private function getRequestBody(): RequestBodyInterface
    {
        return new RequestBody();
    }

    /**
     * Тестирование тела запроса
     */
    public function testBody(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = $this->getRequestBody();
        $body = $body->setBody($array, 'json');
        $this->assertEquals(MimeInterface::JSON, $body->getContentType());
        $this->assertEquals($array, $body->getRaw());
        $this->assertEquals($json, $body->get());
    }

    /**
     * Парсинг контента при смене типа
     */
    public function testParseOnChangeContentType(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = $this->getRequestBody();
        $body = $body->setBody($array);
        $this->assertNull($body->getContentType());
        $this->assertEquals($array, $body->getRaw());
        $body = $body->setContentType('json');
        $this->assertEquals(MimeInterface::JSON, $body->getContentType());
        $this->assertEquals($array, $body->getRaw());
        $this->assertEquals($json, $body->get());
    }

    /**
     * Тестирование тела запроса при пустом типе контента
     */
    public function testWithoutContentType(): void
    {
        $content = 'content';
        $body = $this->getRequestBody();
        $body = $body->setBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals($content, $body->get());
    }

    /**
     * Тестирование тела ответа при пустом теле запроса
     */
    public function testEmptyContent(): void
    {
        $content = '';
        $body = $this->getRequestBody();
        $body->setBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals($content, $body->get());
    }

    /**
     * Тестирование тела запроса при пустом типе контента
     */
    public function testWithoutContentTypeWithArray(): void
    {
        $content = ['foo' => 'bar'];
        $body = $this->getRequestBody();
        $body = $body->setBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals('', $body->get());
    }

    /**
     * Наличие тела запроса
     */
    public function testHas(): void
    {
        $body = $this->getRequestBody();
        $body = $body->setBody('content');
        $this->assertTrue($body->has());
    }

    /**
     * Наличие тела запроса
     */
    public function testHasEmptyString(): void
    {
        $body = $this->getRequestBody();
        $body->setBody('');
        $this->assertFalse($body->has());
    }

    /**
     * Размер запроса
     */
    public function testGetSize(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = $this->getRequestBody();
        $body = $body->setBody($array, 'json');
        $this->assertEquals(mb_strlen($json), $body->getSize());
    }

    /**
     * Прикрепить файлы к телу запроса
     */
    public function testWithFiles(): void
    {
        $body = $this->getRequestBody();
        $this->assertInstanceOf(UploadFileCollectionInterface::class, $body->getUploadFiles());
        $this->assertCount(0, $body->getUploadFiles());
        $body = $body->setUploadFiles($this->getUploadFiles());
        $this->assertInstanceOf(UploadFileCollectionInterface::class, $body->getUploadFiles());
        $this->assertCount(3, $body->getUploadFiles());
    }

    /**
     * Сброс загружаемых файлов
     */
    public function testWithFilesEmpty(): void
    {
        $body = $this->getRequestBody();
        $body = $body->setUploadFiles($this->getUploadFiles());
        $this->assertInstanceOf(UploadFileCollectionInterface::class, $body->getUploadFiles());
        $this->assertCount(3, $body->getUploadFiles());
        $body = $body->setUploadFiles(null);
        $this->assertInstanceOf(UploadFileCollectionInterface::class, $body->getUploadFiles());
        $this->assertCount(0, $body->getUploadFiles());
    }

    /**
     * Добавить загружаемый файл
     */
    public function testAddUploadFile(): void
    {
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/Resources'));
        $body = $this->getRequestBody();
        $this->assertInstanceOf(UploadFileCollectionInterface::class, $body->getUploadFiles());
        $this->assertCount(0, $body->getUploadFiles());
        $body = $body->addUploadFile('file1', $filesystem->factoryFile('./file1.txt'));
        $this->assertInstanceOf(UploadFileCollectionInterface::class, $body->getUploadFiles());
        $this->assertCount(1, $body->getUploadFiles());
        /**
         * @var UploadFileInterface $uploadFile
         */
        $uploadFile = $body->getUploadFiles()[0];
        $this->assertEquals('file1', $uploadFile->getName());
    }

    /**
     * Content type для заголовков
     */
    public function testGetContentTypeHeader(): void
    {
        $body = $this->getRequestBody();
        $this->assertNull($body->getContentTypeHeader());
        $body = $body->setContentType(MimeInterface::FORM);
        $this->assertEquals(MimeInterface::FORM, $body->getContentTypeHeader());
    }

    /**
     * Выставляется multipart/form-data при наличии файлов
     */
    public function testMimeUploadContentTypeSet(): void
    {
        $body = $this->getRequestBody();
        $body = $body->setBody(['foo' => 'bar'], MimeInterface::FORM, $this->getUploadFiles());
        $this->assertEquals(MimeInterface::UPLOAD, $body->getContentType());
    }
}
