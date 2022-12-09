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
    public function parse(string $rawBody)
    {
        return $rawBody;
    }
}
