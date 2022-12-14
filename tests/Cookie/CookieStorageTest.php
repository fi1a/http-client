<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Cookie;

use Fi1a\HttpClient\Cookie\Cookie;
use Fi1a\HttpClient\Cookie\CookieInterface;
use Fi1a\HttpClient\Cookie\CookieStorage;
use Fi1a\HttpClient\Cookie\CookieStorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * Хранилище кук
 */
class CookieStorageTest extends TestCase
{
    /**
     * Возвращает хранилище кук
     */
    private function getStorage(): CookieStorageInterface
    {
        return new CookieStorage();
    }

    /**
     * Возвращает куку
     */
    private function getCookie(): CookieInterface
    {
        return new Cookie([
            'Name' => 'name1',
            'Value' => 'value1',
            'Domain' => 'domain.ru',
            'Path' => '/',
        ]);
    }

    /**
     * Добавить куку
     */
    public function testAddCookie(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $this->assertFalse($storage->addCookie($cookie));
    }

    /**
     * Добавить куку (изменено название)
     */
    public function testAddCookieChangeName(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $cookie = clone $cookie;
        $cookie->setName('name2');
        $this->assertTrue($storage->addCookie($cookie));
    }

    /**
     * Добавить куку (изменено значение)
     */
    public function testAddCookieChangeValue(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $cookie = clone $cookie;
        $cookie->setValue('value2');
        $this->assertTrue($storage->addCookie($cookie));
    }

    /**
     * Добавить куку (изменен домен)
     */
    public function testAddCookieChangeDomain(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $cookie = clone $cookie;
        $cookie->setDomain('new.domain.ru');
        $this->assertTrue($storage->addCookie($cookie));
    }

    /**
     * Добавить куку (изменен путь)
     */
    public function testAddCookieChangePath(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $cookie = clone $cookie;
        $cookie->setPath('/new/path/');
        $this->assertTrue($storage->addCookie($cookie));
    }

    /**
     * Добавить куку (изменен время жизни)
     */
    public function testAddCookieChangeExpires(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $cookie = clone $cookie;
        $cookie->setExpires(100500);
        $this->assertTrue($storage->addCookie($cookie));
    }

    /**
     * Добавить куку (изменен флаг хранения на сессию)
     */
    public function testAddCookieChangeSession(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $cookie = clone $cookie;
        $cookie->setSession(true);
        $this->assertTrue($storage->addCookie($cookie));
        $cookie = clone $cookie;
        $cookie->setSession(false);
        $this->assertTrue($storage->addCookie($cookie));
    }

    /**
     * Добавить куку (не заполненную)
     */
    public function testAddCookieEmpty(): void
    {
        $storage = $this->getStorage();
        $cookie = new Cookie();
        $this->assertFalse($storage->addCookie($cookie));
    }

    public function testGetCookies(): void
    {
        $storage = $this->getStorage();
        $cookie1 = new Cookie([
            'Name' => 'name1',
            'Value' => 'value1',
            'Domain' => 'domain.ru',
            'Path' => '/',
        ]);
        $this->assertTrue($storage->addCookie($cookie1));
        $cookie2 = new Cookie([
            'Name' => 'name2',
            'Value' => 'value2',
            'Domain' => 'domain.ru',
            'Path' => '/',
        ]);
        $this->assertTrue($storage->addCookie($cookie2));
        $cookie3 = new Cookie([
            'Name' => 'name3',
            'Value' => 'value3',
            'Domain' => 'domain.ru',
            'Path' => '/some/path',
        ]);
        $this->assertTrue($storage->addCookie($cookie3));
        $cookie4 = new Cookie([
            'Name' => 'name4',
            'Value' => 'value4',
            'Domain' => 'otherdomain.ru',
            'Path' => '/',
        ]);
        $this->assertTrue($storage->addCookie($cookie4));
        $this->assertCount(2, $storage->getCookiesWithCondidition('domain.ru', '/'));
        $this->assertCount(3, $storage->getCookiesWithCondidition('domain.ru', '/some/path'));
        $this->assertCount(1, $storage->getCookiesWithCondidition('otherdomain.ru', '/'));
    }
}
