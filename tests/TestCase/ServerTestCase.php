<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\TestCase;

use PHPUnit\Framework\TestCase;

/**
 * Тесты с обращением к серверу
 */
class ServerTestCase extends TestCase
{
    protected const HOST = WEB_SERVER_HOST . ':' . WEB_SERVER_PORT;
}
