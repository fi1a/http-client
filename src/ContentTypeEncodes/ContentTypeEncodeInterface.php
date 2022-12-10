<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\ContentTypeEncodes;

/**
 * Парсер типа контента
 */
interface ContentTypeEncodeInterface
{
    /**
     * Осуществляет декодирование контента
     *
     * @return mixed
     */
    public function decode(string $rawBody);

    /**
     * Осуществляет кодирование контента
     *
     * @param mixed $rawBody
     */
    public function encode($rawBody): string;
}
