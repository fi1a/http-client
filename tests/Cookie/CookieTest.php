<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Cookie;

use Fi1a\HttpClient\Cookie\Cookie;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Cookie
 */
class CookieTest extends TestCase
{
    /**
     * Значения по умолчанию
     */
    public function testDefaultValues(): void
    {
        $cookie = new Cookie();
        $this->assertFalse($cookie->getSession());
    }

    /**
     * Проверяет, соответствует ли пути
     */
    public function testMatchPath(): void
    {
        $cookie = new Cookie();
        $cookie->setPath('/');
        $this->assertTrue($cookie->matchPath('/'));
    }

    /**
     * Проверяет, соответствует ли пути
     */
    public function testMatchPathInclude(): void
    {
        $cookie = new Cookie();
        $cookie->setPath('/to');
        $this->assertTrue($cookie->matchPath('/to/path'));
    }

    /**
     * Проверяет, соответствует ли пути
     */
    public function testMatchPathEqual(): void
    {
        $cookie = new Cookie();
        $cookie->setPath('/to/path');
        $this->assertTrue($cookie->matchPath('/to/path'));
    }

    /**
     * Проверяет, соответствует ли пути
     */
    public function testMatchPathPrefix(): void
    {
        $cookie = new Cookie();
        $cookie->setPath('/to/path');
        $this->assertFalse($cookie->matchPath('/to'));
    }

    /**
     * Проверяет, соответствует ли пути
     */
    public function testMatchPathRoot(): void
    {
        $cookie = new Cookie();
        $cookie->setPath('/to/');
        $this->assertTrue($cookie->matchPath('/to'));
    }

    /**
     * Проверяет, соответствует ли пути
     */
    public function testMatchPathRootPath(): void
    {
        $cookie = new Cookie();
        $cookie->setPath('/to/');
        $this->assertFalse($cookie->matchPath('/'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomain(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('domain.ru');
        $this->assertTrue($cookie->matchDomain('domain.ru'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainNull(): void
    {
        $cookie = new Cookie();
        $this->assertTrue($cookie->matchDomain('domain.ru'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainIPNotEqual(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('192.168.1.1');
        $this->assertFalse($cookie->matchDomain('192.168.1.2'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainIPEqual(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('192.168.1.1');
        $this->assertTrue($cookie->matchDomain('192.168.1.1'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainSubdomains(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('.domain.ru');
        $this->assertTrue($cookie->matchDomain('new.domain.ru'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainSubdomainsWithoutDot(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('domain.ru');
        $this->assertTrue($cookie->matchDomain('new.domain.ru'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainSubSubdomains(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('.domain.ru');
        $this->assertTrue($cookie->matchDomain('www.new.domain.ru'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainNotEqual(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('domain.ru');
        $this->assertFalse($cookie->matchDomain('newdomain.ru'));
    }

    /**
     * Проверяет, соответствует ли домен
     */
    public function testMatchDomainEmpty(): void
    {
        $cookie = new Cookie();
        $cookie->setDomain('domain.ru');
        $this->assertFalse($cookie->matchDomain(''));
    }

    /**
     * Кука из пустой строки
     */
    public function testFromStringEmpty(): void
    {
        $this->expectException(LogicException::class);
        $cookie = Cookie::fromString('');
        $cookie->validate();
    }

    /**
     * Кука из строки
     */
    public function testFromString(): void
    {
        $cookie = Cookie::fromString(
            'Name1=value1; Domain=domain.ru; Path=/path/; Expires=100; Max-Age=100; Secure; HttpOnly;'
        );
        $this->assertEquals('Name1', $cookie->getName());
        $this->assertEquals('value1', $cookie->getValue());
        $this->assertEquals('domain.ru', $cookie->getDomain());
        $this->assertEquals('/path/', $cookie->getPath());
        $this->assertEquals(100, $cookie->getExpires());
        $this->assertEquals(100, $cookie->getMaxAge());
        $this->assertTrue($cookie->getSecure());
        $this->assertTrue($cookie->getHttpOnly());
    }
}
