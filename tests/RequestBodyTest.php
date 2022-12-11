<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\RequestBody;
use Fi1a\HttpClient\RequestBodyInterface;
use PHPUnit\Framework\TestCase;

/**
 * Тело запроса
 */
class RequestBodyTest extends TestCase
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
        $body->withBody($array, 'json');
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
        $body->withBody($array);
        $this->assertNull($body->getContentType());
        $this->assertEquals($array, $body->getRaw());
        $body->withContentType('json');
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
        $body->withBody($content);
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
        $body->withBody($content);
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
        $body->withBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals('', $body->get());
    }

    /**
     * Наличие тела запроса
     */
    public function testHas(): void
    {
        $body = $this->getRequestBody();
        $body->withBody('content');
        $this->assertTrue($body->has());
    }

    /**
     * Наличие тела запроса
     */
    public function testHasEmptyString(): void
    {
        $body = $this->getRequestBody();
        $body->withBody('');
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
        $body->withBody($array, 'json');
        $this->assertEquals(mb_strlen($json), $body->getSize());
    }
}
