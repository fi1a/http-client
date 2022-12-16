<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Middlewares;

use Fi1a\HttpClient\Middlewares\MiddlewareCollection;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\ResponseStopMiddleware;
use Fi1a\Unit\HttpClient\Fixtures\Middlewares\Set500StatusMiddleware;
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
        $collection[] = new Set500StatusMiddleware();
        $this->assertCount(2, $collection);
    }

    /**
     * Тестирование сортировки
     */
    public function testSortByField(): void
    {
        $collection = new MiddlewareCollection();
        $collection[] = (new ResponseStopMiddleware())->setSort(600);
        $collection[] = (new Set500StatusMiddleware())->setSort(200);
        $newCollection = $collection->sortByField();
        $this->assertCount(2, $newCollection);
        $this->assertEquals(200, $newCollection[0]->getSort());
        $this->assertEquals(600, $newCollection[1]->getSort());
    }
}
