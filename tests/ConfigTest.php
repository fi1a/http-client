<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\Config;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Конфигурация
 */
class ConfigTest extends TestCase
{
    /**
     * Значения по умолчанию
     */
    public function testDefault(): void
    {
        $config = new Config();
        $this->assertTrue($config->getSslVerify());
        $this->assertEquals(10, $config->getTimeout());
        $this->assertNull($config->getCompress());
    }

    /**
     * Требовать проверки SSL-сертификата
     */
    public function testSslVerify(): void
    {
        $config = new Config();
        $this->assertTrue($config->getSslVerify());
        $config->setSslVerify(false);
        $this->assertFalse($config->getSslVerify());
    }

    /**
     * Таймаут соединения
     */
    public function testTimeout(): void
    {
        $config = new Config();
        $this->assertEquals(10, $config->getTimeout());
        $config->setTimeout(20);
        $this->assertEquals(20, $config->getTimeout());
    }

    /**
     * Сжатие
     */
    public function testCompress(): void
    {
        $config = new Config();
        $this->assertNull($config->getCompress());
        $config->setCompress('gzip');
        $this->assertEquals('gzip', $config->getCompress());
    }

    /**
     * Исключение при не поддерживаемом сжатии
     */
    public function testCompressException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = new Config();
        $config->setCompress('unknown');
    }
}
