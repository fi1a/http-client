<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Proxy;

/**
 * Http proxy
 */
class HttpProxy extends AbstractProxy
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'http';
    }
}
