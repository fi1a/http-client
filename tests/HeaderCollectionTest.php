<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\Header;
use Fi1a\HttpClient\HeaderCollection;
use Fi1a\HttpClient\HeaderCollectionInterface;
use Fi1a\HttpClient\HeaderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Коллекция заголовков
 */
class HeaderCollectionTest extends TestCase
{
    private function getCollection(): HeaderCollectionInterface
    {
        $collection = new HeaderCollection();
        $collection[] = ['Content-Type', 'text/html; charset=utf-8'];
        $collection[] = ['Content-Type', ''];
        $collection[] = new Header('CONTENT-TYPE', 'text/html; charset=utf-8');

        return $collection;
    }

    /**
     * Коллекция заголовков
     */
    public function testCollection(): void
    {
        $collection = $this->getCollection();
        $this->assertCount(3, $collection);
    }

    /**
     * Проверяет наличие заголовка с именем
     */
    public function testHasHeader(): void
    {
        $collection = $this->getCollection();
        $this->assertTrue($collection->hasHeader('Content-Type'));
        $this->assertTrue($collection->hasHeader('CONTENT-TYPE'));
        $this->assertFalse($collection->hasHeader('Content-Range'));
    }

    /**
     * Возвращает заголовок с определенным именем
     */
    public function testGetHeader(): void
    {
        $collection = $this->getCollection();
        $this->assertCount(3, $collection->getHeader('Content-Type'));
        $this->assertCount(3, $collection->getHeader('CONTENT-TYPE'));
        $this->assertCount(0, $collection->getHeader('Content-Range'));
    }

    /**
     * Удалить заголовок с определенным именем
     */
    public function testWithoutHeader(): void
    {
        $collection = $this->getCollection();
        $this->assertTrue($collection->withoutHeader('Content-Type'));
        $this->assertCount(0, $collection->getHeader('Content-Type'));
        $collection = $this->getCollection();
        $this->assertTrue($collection->withoutHeader('CONTENT-TYPE'));
        $this->assertFalse($collection->withoutHeader('CONTENT-TYPE'));
        $this->assertCount(0, $collection->getHeader('Content-Type'));
    }

    /**
     * Возвращает первый найденный заголовок с определенным именем
     */
    public function testGetFirstHeader(): void
    {
        $collection = $this->getCollection();
        $header = $collection->getFirstHeader('Content-Type');
        $this->assertInstanceOf(HeaderInterface::class, $header);
        $this->assertEquals('Content-Type', $header->getName());
        $this->assertNull($collection->getFirstHeader('Content-Range'));
    }

    /**
     * Возвращает первый найденный заголовок с определенным именем
     */
    public function testLastHeader(): void
    {
        $collection = $this->getCollection();
        $header = $collection->getLastHeader('Content-Type');
        $this->assertInstanceOf(HeaderInterface::class, $header);
        $this->assertEquals('CONTENT-TYPE', $header->getName());
        $this->assertNull($collection->getLastHeader('Content-Range'));
    }
}
