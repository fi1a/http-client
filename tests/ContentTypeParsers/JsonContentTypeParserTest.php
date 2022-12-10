<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\ContentTypeParsers;

use Fi1a\HttpClient\ContentTypeEncodes\JsonContentTypeEncode;
use PHPUnit\Framework\TestCase;

/**
 * Парсер json типа контента
 */
class JsonContentTypeParserTest extends TestCase
{
    /**
     * Декодирование
     */
    public function testDecode(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);
        $parser = new JsonContentTypeEncode();
        $this->assertEquals($array, $parser->decode($json));
    }

    /**
     * Кодирование
     */
    public function testEncode(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);
        $parser = new JsonContentTypeEncode();
        $this->assertEquals($json, $parser->encode($array));
    }
}
