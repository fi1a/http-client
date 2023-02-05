<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Proxy;

/**
 * Socks5 proxy
 */
class Socks5Proxy extends AbstractProxy
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'socks5';
    }
}
