<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeEncodes;

use ErrorException;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\UploadFileCollectionInterface;
use Fi1a\HttpClient\UploadFileInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

/**
 * Парсер multipart/form-data типа контента
 */
class MultipartFormDataContentTypeEncode implements ContentTypeEncodeInterface
{
    /**
     * @var string
     */
    private $boundary;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->boundary = md5(rand() . time());
    }

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

        $encoded = '';
        /**
         * @var mixed $value
         */
        foreach ($rawBody as $name => $value) {
            $encoded .= '--' . $this->boundary . "\r\n";
            $encoded .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
            $encoded .= rawurlencode((string) $value) . "\r\n";
        }

        foreach ($uploadFiles as $uploadFile) {
            assert($uploadFile instanceof UploadFileInterface);
            $file = $uploadFile->getFile();
            if (!$file->isExist()) {
                continue;
            }
            if (!$file->canRead()) {
                throw new ErrorException('Нет прав на чтение файла');
            }
            /**
             * @var string $content
             */
            $content = $file->read();
            $encoded .= '--' . $this->boundary . "\r\n";
            $encoded .= 'Content-Disposition: form-data; name="' . $uploadFile->getName()
                . '"; filename="' . $file->getName() . '"' . "\r\n";
            $encoded .= "Content-Type: application/octet-stream\r\n\r\n";
            $encoded .= $content . "\r\n";
        }
        $encoded .= '--' . $this->boundary . "--\r\n";

        return $encoded;
    }

    /**
     * @inheritDoc
     */
    public function getContentTypeHeader(): string
    {
        return MimeInterface::UPLOAD . '; boundary=' . $this->boundary;
    }
}
