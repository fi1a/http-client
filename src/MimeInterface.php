<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Mime
 */
interface MimeInterface
{
    public const PLAIN = 'text/plain';
    public const HTML = 'text/html';
    public const JS = 'text/javascript';
    public const XHTML = 'application/html+xml';
    public const JSON = 'application/json';
    public const XML = 'application/xml';
    public const FORM = 'application/x-www-form-urlencoded';
    public const YAML = 'application/x-yaml';
    public const CSV = 'text/csv';
    public const UPLOAD = 'multipart/form-data';

    /**
     * Возвращает mime
     */
    public function getMime(string $shortcut): string;
}
