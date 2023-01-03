<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\Collection\CollectionInterface;

/**
 * Коллекция промежуточного ПО
 */
interface MiddlewareCollectionInterface extends CollectionInterface
{
    /**
     * Сортирует промежуточное ПО
     */
    public function sortDirect(): MiddlewareCollectionInterface;

    /**
     * Сортирует промежуточное ПО
     */
    public function sortBack(): MiddlewareCollectionInterface;
}
