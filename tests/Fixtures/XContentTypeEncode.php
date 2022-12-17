<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient\Fixtures;

use Fi1a\HttpClient\ContentTypeEncodes\ContentTypeEncodeInterface;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\UploadFileCollectionInterface;

/**
 * Fixture class
 */
class XContentTypeEncode implements ContentTypeEncodeInterface
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
    public function encode($rawBody, UploadFileCollectionInterface $uploadFiles): string
    {
        return $rawBody;
    }

    /**
     * @inheritDoc
     */
    public function getContentTypeHeader(): string
    {
        return MimeInterface::PLAIN;
    }
}
