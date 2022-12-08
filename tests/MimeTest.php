<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\Mime;
use Fi1a\HttpClient\MimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Mime
 */
class MimeTest extends TestCase
{
    /**
     * Возвращает mime
     */
    public function testGetMime(): void
    {
        $mime = new Mime();
        $this->assertEquals(MimeInterface::JSON, $mime->getMime('json'));
        $this->assertEquals('unknown', $mime->getMime('unknown'));
    }

    /**
     * Возвращает mime
     */
    public function testException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $mime = new Mime();
        $mime->getMime('');
    }
}
