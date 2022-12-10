<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\ContentTypeParsers;

use Fi1a\HttpClient\ContentTypeEncodes\FormContentTypeEncode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\LogicException;

/**
 * Парсер application/x-www-form-urlencoded типа контента
 */
class FormContentTypeParserTest extends TestCase
{
    /**
     * Декодирование
     */
    public function testDecode(): void
    {
        $this->expectException(LogicException::class);
        $parser = new FormContentTypeEncode();
        $parser->decode('');
    }

    /**
     * Кодирование
     */
    public function testEncode(): void
    {
        $array = ['foo' => 'bar'];
        $query = http_build_query($array, '', '&');
        $parser = new FormContentTypeEncode();
        $this->assertEquals($query, $parser->encode($array));
    }

    /**
     * Кодирование строки
     */
    public function testEncodeString(): void
    {
        $array = ['foo' => 'bar'];
        $query = http_build_query($array, '', '&');
        $parser = new FormContentTypeEncode();
        $this->assertEquals($query, $parser->encode($query));
    }

    /**
     * Исключение при не поддерживаемом типе
     */
    public function testEncodeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $parser = new FormContentTypeEncode();
        $parser->encode($this);
    }
}
