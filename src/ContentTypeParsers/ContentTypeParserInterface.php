<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeParsers;

/**
 * Парсер типа контента
 */
interface ContentTypeParserInterface
{
    /**
     * Осуществляет парсинг контента
     *
     * @return mixed
     */
    public function parse(string $rawBody);
}
