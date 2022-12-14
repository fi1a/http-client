<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Collection\InstanceCollectionInterface;

/**
 * Коллекция кук
 */
interface CookieCollectionInterface extends InstanceCollectionInterface
{
    /**
     * Возвращает куку по имени
     *
     * @return CookieInterface|false
     */
    public function getByName(string $name);

    /**
     * Возвращает валидные куки
     */
    public function getValid(): CookieCollectionInterface;
}
