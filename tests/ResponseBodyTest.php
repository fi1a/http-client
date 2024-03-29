<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\ResponseBody;
use PHPUnit\Framework\TestCase;

/**
 * Тело ответа
 */
class ResponseBodyTest extends TestCase
{
    /**
     * Тестирование тела ответа
     */
    public function testBody(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = new ResponseBody();
        $body->setBody($json, 'json');
        $this->assertEquals(MimeInterface::JSON, $body->getContentType());
        $this->assertEquals($json, $body->getRaw());
        $this->assertEquals($array, $body->get());
    }

    /**
     * Парсинг контента при смене типа
     */
    public function testParseOnChangeContentType(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $body = new ResponseBody();
        $body->setBody($json);
        $this->assertNull($body->getContentType());
        $this->assertEquals($json, $body->getRaw());
        $this->assertEquals($json, $body->get());
        $body->setContentType('json');
        $this->assertEquals(MimeInterface::JSON, $body->getContentType());
        $this->assertEquals($json, $body->getRaw());
        $this->assertEquals($array, $body->get());
    }

    /**
     * Тестирование тела ответа при пустом типе контента
     */
    public function testWithoutContentType(): void
    {
        $content = 'content';
        $body = new ResponseBody();
        $body->setBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals($content, $body->get());
    }

    /**
     * Тестирование тела ответа при пустом теле запроса
     */
    public function testEmptyContent(): void
    {
        $content = '';
        $body = new ResponseBody();
        $body->setBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals($content, $body->get());
    }

    /**
     * Наличие тела ответа
     */
    public function testHas(): void
    {
        $body = new ResponseBody();
        $body->setBody('content');
        $this->assertTrue($body->has());
    }

    /**
     * Наличие тела ответа
     */
    public function testHasEmptyString(): void
    {
        $body = new ResponseBody();
        $body->setBody('');
        $this->assertFalse($body->has());
    }

    /**
     * Размер тела ответа
     */
    public function testGetSize(): void
    {
        $body = new ResponseBody();
        $body->setBody('content');
        $this->assertTrue($body->has());
        $this->assertEquals(7, $body->getSize());
    }
}
