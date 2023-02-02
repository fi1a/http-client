<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\Uri;
use Fi1a\Http\UriInterface;

/**
 * Возвращает кодированные части Uri
 */
class EncodedUri extends Uri implements EncodedUriInterface
{
    /**
     * @inheritDoc
     */
    public static function create(UriInterface $uri): EncodedUriInterface
    {
        return new EncodedUri($uri->getUri());
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        $host = parent::getHost();
        if (!$host) {
            return $host;
        }
        if (function_exists('idn_to_ascii')) {
            $host = idn_to_ascii($host);
        }

        return $host ?: '';
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return implode(
            '/',
            array_map('rawurlencode', explode('/', parent::getPath()))
        );
    }
}
