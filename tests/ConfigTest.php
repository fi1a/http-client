<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\Config;
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
}
