<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Cookie;

use Fi1a\Config\Parsers\JSONParser;
use Fi1a\Config\Readers\FileReader;
use Fi1a\Config\Writers\FileWriter;
use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\FileInterface;
use Fi1a\Filesystem\Filesystem;
use Fi1a\HttpClient\Cookie\ConfigCookieStorage;
use Fi1a\HttpClient\Cookie\ConfigCookieStorageInterface;
use Fi1a\HttpClient\Cookie\Cookie;
use PHPUnit\Framework\TestCase;

/**
 * Хранилище кук в конфиге
 */
class ConfigCookieStorageTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::getConfigFile()->delete();
    }

    /**
     * Возвращает файл конфигурации
     */
    private static function getConfigFile(): FileInterface
    {
        $filesystem = new Filesystem(new LocalAdapter(__DIR__ . '/../Resources'));

        return $filesystem->factoryFile('./cookie.json');
    }

    /**
     * Возвращает хранилище
     */
    private function getStorage(): ConfigCookieStorageInterface
    {
        $file = self::getConfigFile();

        return new ConfigCookieStorage(
            new FileReader($file),
            new FileWriter($file),
            new JSONParser()
        );
    }

    /**
     * Загрузка кук из конфига
     */
    public function testLoadEmpty(): void
    {
        $storage = $this->getStorage();
        $storage->load();
        $this->assertCount(0, $storage->getCookies());
    }

    /**
     * @depends testLoadEmpty
     */
    public function testSave(): void
    {
        $storage = $this->getStorage();
        $this->assertFalse($storage->save());
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
        $this->assertTrue($storage->save());
    }

    /**
     * Загрузка кук из конфига
     *
     * @depends testSave
     */
    public function testLoad(): void
    {
        $storage = $this->getStorage();
        $storage->load();
        $this->assertCount(2, $storage->getCookies());
    }
}
