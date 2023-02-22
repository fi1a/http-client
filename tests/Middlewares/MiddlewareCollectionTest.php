<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Middlewares;

use Fi1a\HttpClient\Middlewares\MiddlewareCollection;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\ExceptionMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\ResponseStopMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * Коллекция промежуточного ПО
 */
class MiddlewareCollectionTest extends TestCase
{
    /**
     * Тестирование коллекции
     */
    public function testCollection(): void
    {
        $collection = new MiddlewareCollection();
        $collection[] = new ResponseStopMiddleware();
        $collection[] = new ExceptionMiddleware();
        $this->assertCount(2, $collection);
    }

    /**
     * Тестирование сортировки
     */
    public function testSortDirect(): void
    {
        $collection = new MiddlewareCollection();
        $collection[] = (new ResponseStopMiddleware())->setSort(600);
        $collection[] = (new ExceptionMiddleware())->setSort(200);
        $newCollection = $collection->sortDirect();
        $this->assertCount(2, $newCollection);
        $this->assertEquals(200, $newCollection[0]->getSort());
        $this->assertEquals(600, $newCollection[1]->getSort());
    }

    /**
     * Тестирование сортировки
     */
    public function testSortBack(): void
    {
        $collection = new MiddlewareCollection();
        $collection[] = (new ExceptionMiddleware())->setSort(200);
        $collection[] = (new ResponseStopMiddleware())->setSort(600);
        $newCollection = $collection->sortBack();
        $this->assertCount(2, $newCollection);
        $this->assertEquals(600, $newCollection[0]->getSort());
        $this->assertEquals(200, $newCollection[1]->getSort());
    }
}
