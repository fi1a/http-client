<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\Http\Uri;
use Fi1a\HttpClient\EncodedUri;
use Fi1a\HttpClient\EncodedUriInterface;
use PHPUnit\Framework\TestCase;

/**
 * Возвращает кодированные части Uri
 */
class EncodedUriTest extends TestCase
{
    /**
     * Создание EncodedUri из Uri
     */
    public function testCreate(): void
    {
        $uri = EncodedUri::create(new Uri('https://username:password@домен.рф:8080/путь/до/?foo=Один два#fragment'));
        $this->assertInstanceOf(EncodedUriInterface::class, $uri);
        $this->assertEquals(
            'https://username:password@xn--d1acufc.xn--p1ai:8080/%D0%BF%D1%83%D1%82%D1%8C/%D0%B4%D0%BE/'
            . '?foo=%D0%9E%D0%B4%D0%B8%D0%BD+%D0%B4%D0%B2%D0%B0#fragment',
            $uri->getUri()
        );
    }

    /**
     * Хост
     */
    public function testHost(): void
    {
        $uri = new EncodedUri('https://host.ru/');
        $this->assertEquals('host.ru', $uri->getHost());
        $uri = $uri->withHost('домен.рф');
        $this->assertEquals('xn--d1acufc.xn--p1ai', $uri->getHost());
    }

    /**
     * Часть пути URI
     */
    public function testPath(): void
    {
        $uri = new EncodedUri('https://host.ru/some/path/');
        $this->assertEquals('/some/path/', $uri->getPath());
        $uri = $uri->withPath('/путь/до/файла.pdf');
        $this->assertEquals(
            '/%D0%BF%D1%83%D1%82%D1%8C/%D0%B4%D0%BE/%D1%84%D0%B0%D0%B9%D0%BB%D0%B0.pdf',
            $uri->getPath()
        );
    }
}
