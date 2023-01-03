<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Middlewares;

use Fi1a\Collection\Collection;

/**
 * Коллекция промежуточного ПО
 */
class MiddlewareCollection extends Collection implements MiddlewareCollectionInterface
{
    /**
     * @inheritDoc
     */
    public function __construct(?array $data = null)
    {
        parent::__construct(MiddlewareInterface::class, $data);
    }

    /**
     * @inheritDoc
     */
    public function sortDirect(): MiddlewareCollectionInterface
    {
        /**
         * @var MiddlewareInterface[] $middlewares
         */
        $middlewares = $this->getArrayCopy();
        usort(
            $middlewares,
            function (MiddlewareInterface $middlewareA, MiddlewareInterface $middlewareB): int {
                return $middlewareA->getSort() - $middlewareB->getSort();
            }
        );

        return new MiddlewareCollection($middlewares);
    }

    /**
     * @inheritDoc
     */
    public function sortBack(): MiddlewareCollectionInterface
    {
        /**
         * @var MiddlewareInterface[] $middlewares
         */
        $middlewares = $this->getArrayCopy();
        usort(
            $middlewares,
            function (MiddlewareInterface $middlewareA, MiddlewareInterface $middlewareB): int {
                return $middlewareB->getSort() - $middlewareA->getSort();
            }
        );

        return new MiddlewareCollection($middlewares);
    }
}
