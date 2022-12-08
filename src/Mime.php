<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use InvalidArgumentException;

/**
 * Mime
 */
class Mime implements MimeInterface
{
    /**
     * @var string[]
     */
    private static $shortcuts = [
        'plain' => self::PLAIN,
        'html' => self::HTML,
        'js' => self::JS,
        'javascript' => self::JS,
        'xhtml' => self::XHTML,
        'json' => self::JSON,
        'xml' => self::XML,
        'form' => self::FORM,
        'yaml' => self::YAML,
        'csv' => self::CSV,
        'upload' => self::UPLOAD,
        'text' => self::PLAIN,
    ];

    /**
     * @inheritDoc
     */
    public function getMime(string $shortcut): string
    {
        if (!$shortcut) {
            throw new InvalidArgumentException('Mime не может быть пустым');
        }

        return self::$shortcuts[$shortcut] ?? $shortcut;
    }
}
