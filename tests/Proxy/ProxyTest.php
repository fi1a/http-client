<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Proxy;

use Fi1a\HttpClient\Proxy\HttpProxy;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\Proxy\Socks5Proxy;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Proxy
 */
class ProxyTest extends TestCase
{
    /**
     * Провайдер данных для тестов
     *
     * @return mixed[][]
     */
    public function dataProxyProvider(): array
    {
        return [
            [
                new HttpProxy(
                    '127.0.0.1',
                    5000,
                    'username',
                    'password'
                ),
            ],
            [
                new Socks5Proxy(
                    '127.0.0.1',
                    5000,
                    'username',
                    'password'
                ),
            ],
        ];
    }

    /**
     * Http прокси
     */
    public function testConstructHttpProxy(): void
    {
        $proxy = new HttpProxy(
            '127.0.0.1',
            5000,
            'username',
            'password'
        );
        $this->assertEquals('127.0.0.1', $proxy->getHost());
        $this->assertEquals(5000, $proxy->getPort());
        $this->assertEquals('username', $proxy->getUserName());
        $this->assertEquals('password', $proxy->getPassword());
    }

    /**
     * Socks5 прокси
     */
    public function testConstructSocks5Proxy(): void
    {
        $proxy = new Socks5Proxy(
            '127.0.0.1',
            5000,
            'username',
            'password'
        );
        $this->assertEquals('127.0.0.1', $proxy->getHost());
        $this->assertEquals(5000, $proxy->getPort());
        $this->assertEquals('username', $proxy->getUserName());
        $this->assertEquals('password', $proxy->getPassword());
    }

    /**
     * Хост
     *
     * @dataProvider dataProxyProvider
     */
    public function testHost(ProxyInterface $proxy): void
    {
        $this->assertEquals('127.0.0.1', $proxy->getHost());
        $proxy->setHost('127.0.0.2');
        $this->assertEquals('127.0.0.2', $proxy->getHost());
    }

    /**
     * Хост
     *
     * @dataProvider dataProxyProvider
     */
    public function testHostException(ProxyInterface $proxy): void
    {
        $this->expectException(InvalidArgumentException::class);
        $proxy->setHost('');
    }

    /**
     * Порт
     *
     * @dataProvider dataProxyProvider
     */
    public function testPort(ProxyInterface $proxy): void
    {
        $this->assertEquals(5000, $proxy->getPort());
        $proxy->setPort(6000);
        $this->assertEquals(6000, $proxy->getPort());
    }

    /**
     * Порт
     *
     * @dataProvider dataProxyProvider
     */
    public function testPortException(ProxyInterface $proxy): void
    {
        $this->expectException(InvalidArgumentException::class);
        $proxy->setPort(0);
    }

    /**
     * Пользователь для авторизации
     *
     * @dataProvider dataProxyProvider
     */
    public function testUsername(ProxyInterface $proxy): void
    {
        $this->assertEquals('username', $proxy->getUserName());
        $proxy->setUserName('username2');
        $this->assertEquals('username2', $proxy->getUserName());
    }

    /**
     * Пользователь для авторизации
     *
     * @dataProvider dataProxyProvider
     */
    public function testUsernameEmpty(ProxyInterface $proxy): void
    {
        $this->assertEquals('username', $proxy->getUserName());
        $proxy->setUserName(null);
        $this->assertNull($proxy->getUserName());
        $proxy->setUserName('');
        $this->assertEquals('', $proxy->getUserName());
    }

    /**
     * Пароль для авторизации
     *
     * @dataProvider dataProxyProvider
     */
    public function testPassword(ProxyInterface $proxy): void
    {
        $this->assertEquals('password', $proxy->getPassword());
        $proxy->setPassword('password2');
        $this->assertEquals('password2', $proxy->getPassword());
    }

    /**
     * Пароль для авторизации
     *
     * @dataProvider dataProxyProvider
     */
    public function testPasswordEmpty(ProxyInterface $proxy): void
    {
        $this->assertEquals('password', $proxy->getPassword());
        $proxy->setPassword(null);
        $this->assertNull($proxy->getPassword());
        $proxy->setPassword('');
        $this->assertEquals('', $proxy->getPassword());
    }
}
