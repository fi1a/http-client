<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Fixtures\Proxy;

use Fi1a\HttpClient\Proxy\AbstractProxy;

/**
 * Proxy для тестов
 */
class FixtureProxy extends AbstractProxy
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'test';
    }
}
