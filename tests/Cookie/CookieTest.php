<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Cookie;

use Fi1a\HttpClient\Cookie\Cookie;
use InvalidArgumentException;
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
        $this->assertNull($cookie->getName());
        $this->assertNull($cookie->getValue());
        $this->assertNull($cookie->getDomain());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertNull($cookie->getMaxAge());
        $this->assertNull($cookie->getExpires());
        $this->assertFalse($cookie->getSecure());
        $this->assertFalse($cookie->getHttpOnly());
        $this->assertFalse($cookie->getSession());
    }

    /**
     * Имя
     */
    public function testName(): void
    {
        $cookie = new Cookie();
        $cookie->setName('name');
        $this->assertEquals('name', $cookie->getName());
    }

    /**
     * Имя (исключение при пустой строке)
     */
    public function testNameException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cookie = new Cookie();
        $cookie->setName('');
    }

    /**
     * Имя (исключение при недопустимых символах)
     */
    public function testNameSymbolsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cookie = new Cookie();
        $cookie->setName('\x20');
    }

    /**
     * Значение
     */
    public function testValue(): void
    {
        $cookie = new Cookie();
        $cookie->setValue('value');
        $this->assertEquals('value', $cookie->getValue());
    }

    /**
     * Домен
     */
    public function testDomain(): void
    {
        $cookie = new Cookie();
        $cookie->setValue('domain');
        $this->assertEquals('domain', $cookie->getValue());
    }

    /**
     * Путь
     */
    public function testPath(): void
    {
        $cookie = new Cookie();
        $cookie->setPath('/path/');
        $this->assertEquals('/path/', $cookie->getPath());
    }

    /**
     * Время жизни
     */
    public function testMaxAge(): void
    {
        $cookie = new Cookie();
        $this->assertNull($cookie->getExpires());
        $cookie->setMaxAge(100);
        $this->assertEquals(100, $cookie->getMaxAge());
        $this->assertIsInt($cookie->getExpires());
        $cookie->setMaxAge(null);
        $this->assertNull($cookie->getExpires());
    }

    /**
     * UNIX timestamp когда кука истечет
     */
    public function testExpires(): void
    {
        $cookie = new Cookie();
        $cookie->setExpires(100);
        $this->assertIsInt($cookie->getExpires());
        $cookie->setExpires(null);
        $this->assertNull($cookie->getExpires());
        $cookie->setExpires('13.12.2022 00:00:00');
        $this->assertIsInt($cookie->getExpires());
    }

    /**
     * Кука истекла
     */
    public function testIsExpired(): void
    {
        $cookie = new Cookie();
        $cookie->setExpires(time());
        $this->assertTrue($cookie->isExpired());
    }

    /**
     * Кука истекла
     */
    public function testIsNotExpired(): void
    {
        $cookie = new Cookie();
        $cookie->setExpires(time() + 100);
        $this->assertFalse($cookie->isExpired());
    }

    /**
     * Флаг secure
     */
    public function testSecure(): void
    {
        $cookie = new Cookie();
        $cookie->setSecure(true);
        $this->assertTrue($cookie->getSecure());
    }

    /**
     * Флаг HttpOnly
     */
    public function testHttpOnly(): void
    {
        $cookie = new Cookie();
        $cookie->setHttpOnly(true);
        $this->assertTrue($cookie->getHttpOnly());
    }

    /**
     * Действует только на эту сессию
     */
    public function testSession(): void
    {
        $cookie = new Cookie();
        $cookie->setSession(true);
        $this->assertTrue($cookie->getSession());
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
     * Пустое название
     */
    public function testValidateName(): void
    {
        $this->expectException(LogicException::class);
        $cookie = new Cookie();
        $cookie->validate();
    }

    /**
     * Пустое значение
     */
    public function testValidateValue(): void
    {
        $this->expectException(LogicException::class);
        $cookie = new Cookie();
        $cookie->setName('cookie');
        $cookie->validate();
    }

    /**
     * Пустой домен
     */
    public function testValidateDomain(): void
    {
        $this->expectException(LogicException::class);
        $cookie = new Cookie();
        $cookie->setName('cookie');
        $cookie->setValue('value');
        $cookie->validate();
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
