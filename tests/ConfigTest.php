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
        $this->assertTrue($config->getAllowRedirects());
        $this->assertEquals(10, $config->getMaxRedirects());
        $this->assertFalse($config->getCookie());
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

    /**
     * Разрешены редиректы или нет
     */
    public function testAllowRedirects(): void
    {
        $config = new Config();
        $this->assertTrue($config->getAllowRedirects());
        $config->setAllowRedirects(false);
        $this->assertFalse($config->getAllowRedirects());
    }

    /**
     * Максимальное число редиректов
     */
    public function testMaxRedirects(): void
    {
        $config = new Config();
        $this->assertEquals(10, $config->getMaxRedirects());
        $config->setMaxRedirects(0);
        $this->assertEquals(0, $config->getMaxRedirects());
    }

    /**
     * Максимальное число редиректов (исключение при отрицательном значении)
     */
    public function testMaxRedirectsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = new Config();
        $config->setMaxRedirects(-1);
    }

    /**
     * Использовать куки или нет
     */
    public function testCookie(): void
    {
        $config = new Config();
        $this->assertFalse($config->getCookie());
        $config->setCookie(true);
        $this->assertTrue($config->getCookie());
    }
}
