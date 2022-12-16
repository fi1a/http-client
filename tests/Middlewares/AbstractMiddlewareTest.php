<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Middlewares;

use Fi1a\Unit\HttpClient\Fixtures\Middlewares\StopMiddleware;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Методы абстрактного класса промежуточного ПО
 */
class AbstractMiddlewareTest extends TestCase
{
    /**
     * Сортировка
     */
    public function testSort(): void
    {
        $middleware = new StopMiddleware();
        $this->assertEquals(500, $middleware->getSort());
        $middleware->setSort(200);
        $this->assertEquals(200, $middleware->getSort());
    }

    /**
     * Исключение при сортировке меньше 0
     */
    public function testSortException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $middleware = new StopMiddleware();
        $middleware->setSort(-1);
    }
}
