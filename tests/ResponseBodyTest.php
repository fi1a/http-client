<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\MimeInterface;
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
        $body->withBody($json, 'json');
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
        $body->withBody($json);
        $this->assertNull($body->getContentType());
        $this->assertEquals($json, $body->getRaw());
        $this->assertEquals($json, $body->get());
        $body->withContentType('json');
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
        $body = new ResponseBody();
        $body->withBody($content);
        $this->assertEquals($content, $body->getRaw());
        $this->assertEquals($content, $body->get());
    }

    /**
     * Наличие тела ответа
     */
    public function testHas(): void
    {
        $body = new ResponseBody();
        $body->withBody('content');
        $this->assertTrue($body->has());
    }

    /**
     * Наличие тела ответа
     */
    public function testHasEmptyString(): void
    {
        $body = new ResponseBody();
        $body->withBody('');
        $this->assertFalse($body->has());
    }
}
