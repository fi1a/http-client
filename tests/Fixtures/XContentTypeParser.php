<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Fixtures;

use Fi1a\HttpClient\ContentTypeParsers\ContentTypeParserInterface;

/**
 * Fixture class
 */
class XContentTypeParser implements ContentTypeParserInterface
{
    /**
     * @inheritDoc
     */
    public function decode(string $rawBody)
    {
        return $rawBody;
    }

    /**
     * @inheritDoc
     */
    public function encode($rawBody): string
    {
        return $rawBody;
    }
}
