<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\ContentTypeParsers;

use Fi1a\HttpClient\ContentTypeParsers\JsonContentTypeParser;
use PHPUnit\Framework\TestCase;

/**
 * Парсер json типа контента
 */
class JsonContentTypeParserTest extends TestCase
{
    /**
     * Парсинг
     */
    public function testParse(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);
        $parser = new JsonContentTypeParser();
        $this->assertEquals($array, $parser->parse($json));
    }
}
