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
     * Возвращает хранилище cookie
     */
    private function getStorage(): CookieStorageInterface
    {
        return new CookieStorage();
    }

    /**
     * Возвращает cookie
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
     * Добавить cookie
     */
    public function testAddCookie(): void
    {
        $storage = $this->getStorage();
        $cookie = $this->getCookie();
        $this->assertTrue($storage->addCookie($cookie));
        $this->assertFalse($storage->addCookie($cookie));
    }

    /**
     * Добавить cookie (изменено название)
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
     * Добавить cookie (изменено значение)
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
     * Добавить cookie (изменен домен)
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
     * Добавить cookie (изменен путь)
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
     * Добавить cookie (изменен время жизни)
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
     * Добавить cookie (изменен флаг хранения на сессию)
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
     * Добавить cookie (не заполненную)
     */
    public function testAddCookieEmpty(): void
    {
        $storage = $this->getStorage();
        $cookie = new Cookie();
        $this->assertFalse($storage->addCookie($cookie));
    }

    /**
     *  Возвращает коллекцию cookie для домена и пути
     */
    public function testGetCookiesWithCondition(): void
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
        $this->assertCount(2, $storage->getCookiesWithCondition('domain.ru', '/'));
        $this->assertCount(3, $storage->getCookiesWithCondition('domain.ru', '/some/path'));
        $this->assertCount(1, $storage->getCookiesWithCondition('otherdomain.ru', '/'));
    }

    private function addCookies(CookieStorageInterface $storage): void
    {
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
            'Domain' => 'new-domain.ru',
            'Path' => '/',
        ]);
        $this->assertTrue($storage->addCookie($cookie3));
        $cookie4 = new Cookie([
            'Name' => 'name4',
            'Value' => 'value4',
            'Domain' => 'new-domain.ru',
            'Path' => '/path/',
        ]);
        $this->assertTrue($storage->addCookie($cookie4));
        $cookie5 = new Cookie([
            'Name' => 'name1',
            'Value' => 'value1',
            'Domain' => 'new-domain.ru',
            'Path' => '/path/',
        ]);
        $this->assertTrue($storage->addCookie($cookie5));
        $cookie6 = new Cookie([
            'Name' => 'name1',
            'Value' => 'value1',
            'Domain' => 'new-domain.ru',
            'Path' => '/path/other/',
        ]);
        $this->assertTrue($storage->addCookie($cookie6));
    }

    /**
     * Удаление cookie по имени
     */
    public function testDeleteCookieByName(): void
    {
        $storage = $this->getStorage();
        $this->addCookies($storage);
        $storage->deleteCookie('name1');
        $this->assertCount(3, $storage->getCookies());
    }

    /**
     * Удаление cookie по имени и домену
     */
    public function testDeleteCookieByNameAndDomain(): void
    {
        $storage = $this->getStorage();
        $this->addCookies($storage);
        $storage->deleteCookie('name1', 'domain.ru');
        $this->assertCount(5, $storage->getCookies());
    }

    /**
     * Удаление cookie по имени и домену
     */
    public function testDeleteCookieByNameAndDomainAndPath(): void
    {
        $storage = $this->getStorage();
        $this->addCookies($storage);
        $storage->deleteCookie('name1', 'new-domain.ru', '/path/');
        $this->assertCount(5, $storage->getCookies());
    }
}
