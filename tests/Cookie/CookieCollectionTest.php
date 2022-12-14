<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Cookie;

use Fi1a\HttpClient\Cookie\Cookie;
use Fi1a\HttpClient\Cookie\CookieCollection;
use PHPUnit\Framework\TestCase;

/**
 * Коллекция кук
 */
class CookieCollectionTest extends TestCase
{
    /**
     * Тестирование коллекции
     */
    public function testCollection(): void
    {
        $collection = new CookieCollection();
        $collection[] = ['Name' => 'name1', 'Value' => 'value1'];
        $collection[] = new Cookie(['Name' => 'name2', 'Value' => 'value2']);
        $this->assertCount(2, $collection);
    }

    /**
     * Возвращает куку по названию
     */
    public function testGetByName(): void
    {
        $collection = new CookieCollection();
        $collection[] = ['Name' => 'name1', 'Value' => 'value1'];
        $collection[] = new Cookie(['Name' => 'name2', 'Value' => 'value2']);
        $this->assertFalse($collection->getByName('not-exists'));
        $cookie = $collection->getByName('name1');
        $this->assertEquals('name1', $cookie->getName());
    }

    /**
     * Возвращает валидные куки
     */
    public function testGetValid(): void
    {
        $collection = new CookieCollection();
        $collection[] = ['Name' => 'name1', 'Value' => 'value1', 'Domain' => 'domain.ru', 'Path' => '/'];
        $collection[] = new Cookie(['Name' => 'name2', 'Value' => 'value2', 'Domain' => 'domain.ru', 'Path' => '/']);
        $collection[] = new Cookie();
        $this->assertCount(2, $collection->getValid());
    }
}
