<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use InvalidArgumentException;

/**
 * Абстрактный класс промежуточного ПО
 */
abstract class AbstractMiddleware implements MiddlewareInterface
{
    /**
     * @var int
     */
    protected $sort = 500;

    /**
     * @inheritDoc
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @inheritDoc
     */
    public function setSort(int $sort)
    {
        if ($sort < 0) {
            throw new InvalidArgumentException('Сортировка должна быть больше или равной 0');
        }

        $this->sort = $sort;

        return $this;
    }
}
