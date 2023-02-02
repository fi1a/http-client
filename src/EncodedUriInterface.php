<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\UriInterface;

/**
 * Декоратор кодированного uri
 */
interface EncodedUriInterface extends UriInterface
{
    /**
     * Создать экземпляр от UriInterface
     */
    public static function create(UriInterface $uri): EncodedUriInterface;
}
