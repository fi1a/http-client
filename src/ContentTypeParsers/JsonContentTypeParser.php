<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeParsers;

/**
 * Парсер json типа контента
 */
class JsonContentTypeParser implements ContentTypeParserInterface
{
    /**
     * @inheritDoc
     */
    public function decode(string $rawBody)
    {
        return json_decode($rawBody, true);
    }

    /**
     * @inheritDoc
     */
    public function encode($rawBody): string
    {
        return json_encode($rawBody);
    }
}
