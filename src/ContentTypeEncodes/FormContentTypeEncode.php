<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeEncodes;

use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\UploadFileCollectionInterface;
use Fi1a\HttpClient\UploadFileInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

/**
 * Парсер application/x-www-form-urlencoded типа контента
 */
class FormContentTypeEncode implements ContentTypeEncodeInterface
{
    /**
     * @inheritDoc
     */
    public function decode(string $rawBody)
    {
        throw new LogicException('Не поддерживается');
    }

    /**
     * @inheritDoc
     */
    public function encode($rawBody, UploadFileCollectionInterface $uploadFiles): string
    {
        if ((!is_array($rawBody) && !$rawBody) || is_string($rawBody)) {
            return (string) $rawBody;
        }
        if (!is_array($rawBody)) {
            throw new InvalidArgumentException('Не является массивом');
        }
        foreach ($uploadFiles as $uploadFile) {
            assert($uploadFile instanceof UploadFileInterface);
            $file = $uploadFile->getFile();
            if (!$file->isExist()) {
                continue;
            }
            $rawBody[$uploadFile->getName()] = $file->getPath();
        }

        return http_build_query($rawBody, '', '&');
    }

    /**
     * @inheritDoc
     */
    public function getContentTypeHeader(): string
    {
        return MimeInterface::FORM;
    }
}
