<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeEncodes;

use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\UploadFileCollectionInterface;
use Fi1a\HttpClient\UploadFileInterface;

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
    public function encode($rawBody, UploadFileCollectionInterface $uploadFiles): string
    {
        if (is_array($rawBody)) {
            foreach ($uploadFiles as $uploadFile) {
                assert($uploadFile instanceof UploadFileInterface);
                $file = $uploadFile->getFile();
                if (!$file->isExist()) {
                    continue;
                }
                $rawBody[$uploadFile->getName()] = $file->getPath();
            }
        }

        return json_encode($rawBody);
    }

    /**
     * @inheritDoc
     */
    public function getContentTypeHeader(): string
    {
        return MimeInterface::JSON;
    }
}
