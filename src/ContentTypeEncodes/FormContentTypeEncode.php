<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeEncodes;

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
    public function encode($rawBody): string
    {
        if (!$rawBody || is_string($rawBody)) {
            return (string) $rawBody;
        }
        if (!is_array($rawBody)) {
            throw new InvalidArgumentException('Не является массивом');
        }

        return http_build_query($rawBody, '', '&');
    }
}
