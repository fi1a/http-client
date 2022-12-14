<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Config\Parsers\ParserInterface;
use Fi1a\Config\Readers\ReaderInterface;
use Fi1a\Config\Writers\WriterInterface;

/**
 * Хранилище кук в конфиге
 */
interface ConfigCookieStorageInterface extends CookieStorageInterface
{
    public function __construct(ReaderInterface $reader, WriterInterface $writer, ParserInterface $parser);

    /**
     * Загружает куки из файла
     */
    public function load(): bool;

    /**
     * Сохраняет куки в файл
     */
    public function save(): bool;
}
