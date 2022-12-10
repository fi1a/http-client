<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeEncodes;

/**
 * Парсер json типа контента
 */
class JsonContentTypeEncode implements ContentTypeEncodeInterface
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
