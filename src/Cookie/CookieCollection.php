<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Http\CookieCollection as HttpCookieCollection;

/**
 * Коллекция cookie
 */
class CookieCollection extends HttpCookieCollection implements CookieCollectionInterface
{
    /**
     * @inheritDoc
     */
    protected function factory($key, $value)
    {
        return new Cookie((array) $value);
    }
}
