<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\ConfigInterface;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\ResponseInterface;

/**
 * Абстрактный класс обработчика запросов
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Устанавливает полученное тело ответа в объет ответа
     */
    protected function setBody(string $body, ResponseInterface $response): void
    {
        $mime = MimeInterface::HTML;
        $contentTypeHeader = $response->getLastHeader('Content-Type');
        if ($contentTypeHeader) {
            $contentType = $contentTypeHeader->getValue();
            if ($contentType) {
                $mime = $contentType;
                if (preg_match('#(.+); charset=(.+)#mui', $contentType, $matches) > 0) {
                    $mime = $matches[1];
                    $response->withEncoding($matches[2]);
                }
            }
        }
        $response->withBody($body, $mime);
    }

    /**
     * Распаковывает тело сообщения
     */
    protected function decompress(string $body, ResponseInterface $response): string
    {
        $contEncodingHeader = $response->getLastHeader('Content-Encoding');
        if (!$contEncodingHeader || !$body) {
            return $body;
        }
        $encoding = mb_strtolower((string) $contEncodingHeader->getValue());
        if ($encoding === 'gzip') {
            $compressed = substr($body, 10, -8);

            return gzinflate($compressed);
        }

        return $body;
    }
}
