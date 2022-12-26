<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use Fi1a\Http\Mime;
use Fi1a\HttpClient\ContentTypeEncodes\ContentTypeEncodeInterface;
use InvalidArgumentException;

/**
 * Реестр парсеров типов контента
 */
class ContentTypeEncodeRegistry implements ContentTypeEncodeRegistryInterface
{
    /**
     * @var string[]
     */
    private static $parsers = [];

    /**
     * @inheritDoc
     */
    public static function add(string $contentType, string $parser): void
    {
        if (!$contentType) {
            throw new InvalidArgumentException(
                'Тип контента не может быть пустым'
            );
        }
        if (!is_subclass_of($parser, ContentTypeEncodeInterface::class)) {
            throw new InvalidArgumentException(
                'Парсер должен реализовывать интерфейс ' . ContentTypeEncodeInterface::class
            );
        }
        if (static::has($contentType)) {
            throw new InvalidArgumentException(
                sprintf('Парсер для типа контента "%s" уже имеется', $contentType)
            );
        }

        static::$parsers[static::getParserKey($contentType)] = $parser;
    }

    /**
     * @inheritDoc
     */
    public static function has(string $contentType): bool
    {
        return array_key_exists(static::getParserKey($contentType), static::$parsers);
    }

    /**
     * @inheritDoc
     */
    public static function delete(string $contentType): bool
    {
        if (!static::has($contentType)) {
            return false;
        }
        unset(static::$parsers[static::getParserKey($contentType)]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function get(string $contentType)
    {
        if (!static::has($contentType)) {
            return false;
        }

        $class = static::$parsers[static::getParserKey($contentType)];
        /**
         * @var ContentTypeEncodeInterface $instance
         * @psalm-suppress InvalidStringClass
         */
        $instance = new $class();

        return $instance;
    }

    /**
     * Возвращает ключ на основе типа контента
     */
    private static function getParserKey(string $contentType): string
    {
        return Mime::getMime(mb_strtolower($contentType));
    }
}
