<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;
use Fi1a\HttpClient\Handlers\Exceptions\ErrorException;
use Fi1a\HttpClient\Handlers\Exceptions\TimeoutErrorException;
use Fi1a\HttpClient\HeaderInterface;
use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\Response;
use Fi1a\HttpClient\ResponseInterface;
use Fi1a\HttpClient\UriInterface;

use const STREAM_CLIENT_CONNECT;

/**
 * Stream-обработчик запросов
 */
class StreamHandler extends AbstractHandler
{
    private const STREAM_READ_LENGTH = 32768;

    /**
     * @inheritDoc
     */
    public function send(RequestInterface $request, Response $response): ResponseInterface
    {
        $resource = $this->connect($request->getUri());
        $this->sendRequest($resource, $request);
        $this->getHeaders($resource, $response);
        $rawBody = $this->getRawBody($resource, $response);
        $rawBody = $this->decompress($rawBody, $response);
        $this->setRawBody($rawBody, $response);
        $this->disconnect($resource);

        return $response;
    }

    /**
     * Распаковывает тело сообщения
     */
    private function decompress(string $rawBody, ResponseInterface $response): string
    {
        $contEncodingHeader = $response->getLastHeader('Content-Encoding');
        if (!$contEncodingHeader || !$rawBody) {
            return $rawBody;
        }
        $encoding = mb_strtolower((string) $contEncodingHeader->getValue());
        if ($encoding === 'gzip') {
            $compressed = substr($rawBody, 10, -8);

            return gzinflate($compressed);
        }

        return $rawBody;
    }

    /**
     * Transfer-Encoding: chunked
     *
     * @param resource $resource
     */
    private function getRawBodyChunked($resource): string
    {
        $rawBody = '';

        while (!feof($resource)) {
            $line = $this->readContentLine($resource, self::STREAM_READ_LENGTH);
            $this->checkReadErrors($resource, $line);
            if ($line === "\r\n") {
                continue;
            }
            /**
             * @psalm-suppress PossiblyFalseArgument
             */
            $bufLength = hexdec($line);
            $buf = $this->readContents($resource, $bufLength);
            $this->checkReadErrors($resource, $buf);
            /**
             * @psalm-suppress PossiblyFalseOperand
             */
            $rawBody .= $buf;
        }

        return $rawBody;
    }

    /**
     * Не кодированный ответ
     *
     * @param resource $resource
     */
    private function getRawBodyNoEncoding($resource, ?int $contentLength): string
    {
        $rawBody = '';

        while (!feof($resource) && (is_null($contentLength) || $contentLength > 0)) {
            $bufLength = is_null($contentLength) || $contentLength > self::STREAM_READ_LENGTH
                ? self::STREAM_READ_LENGTH
                : $contentLength;
            $buf = $this->readContents($resource, $bufLength);
            $this->checkReadErrors($resource, $buf);
            /**
             * @psalm-suppress PossiblyFalseArgument
             */
            if (!is_null($contentLength)) {
                $contentLength -= mb_strlen($buf);
            }
            /**
             * @psalm-suppress PossiblyFalseOperand
             */
            $rawBody .= $buf;
        }

        return $rawBody;
    }

    /**
     * Тело ответа
     *
     * @param resource $resource
     */
    private function getRawBody($resource, ResponseInterface $response): string
    {
        $transferEncoding = $response->getLastHeader('Transfer-Encoding');
        if ($transferEncoding && $transferEncoding->getValue() === 'chunked') {
            return $this->getRawBodyChunked($resource);
        }

        $contentLength = null;
        $contentLengthHeader = $response->getLastHeader('Content-Length');
        if ($contentLengthHeader) {
            $contentLength = (int) $contentLengthHeader->getValue();
        }

        return $this->getRawBodyNoEncoding($resource, $contentLength);
    }

    /**
     * Устанавливает полученное тело ответа в объет ответа
     */
    private function setRawBody(string $rawBody, ResponseInterface $response): void
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
        $response->withBody($rawBody, $mime);
    }

    /**
     * Чтение из потока
     *
     * @param resource $resource
     *
     * @return false|string
     */
    protected function readContents($resource, int $length)
    {
        return stream_get_contents($resource, $length);
    }

    /**
     * Чтение из потока
     *
     * @param resource $resource
     *
     * @return false|string
     */
    protected function readContentLine($resource, int $length)
    {
        return fgets($resource, $length);
    }

    /**
     * Возвращает мета данные
     *
     * @param resource $resource
     *
     * @return mixed[]
     */
    protected function getMetaData($resource): array
    {
        return stream_get_meta_data($resource);
    }

    /**
     * Заголовки ответа
     *
     * @param resource $resource
     *
     * @throws ErrorException
     * @throws TimeoutErrorException
     */
    private function getHeaders($resource, ResponseInterface $response): void
    {
        while (!feof($resource)) {
            $headerLine = $this->readContentLine($resource, self::STREAM_READ_LENGTH);
            if ($headerLine === "\r\n") {
                break;
            }

            $this->checkReadErrors($resource, $headerLine);

            /**
             * @psalm-suppress PossiblyFalseArgument
             */
            if (preg_match('#^HTTP/(\S+) (\d+) (.+)\r\n$#', $headerLine, $httpVersionAndStatus)) {
                $response->withStatus((int) $httpVersionAndStatus[2], $httpVersionAndStatus[3]);
                $response->withProtocolVersion(trim($httpVersionAndStatus[1]));

                continue;
            }

            /**
             * @psalm-suppress PossiblyFalseArgument
             */
            [$headerName, $headerValue] = array_map('trim', explode(':', $headerLine, 2));
            $response->withHeader($headerName, $headerValue);
        }
    }

    /**
     * Проверка ошибок
     *
     * @param resource $resource
     * @param string|false $line
     *
     * @return void
     *
     * @throws ErrorException
     * @throws TimeoutErrorException
     */
    private function checkReadErrors($resource, $line)
    {
        if ($line === false) {
            $this->disconnect($resource);

            throw new ErrorException('Ошибка при чтении потока');
        }
        if ($this->config->getTimeout() > 0) {
            $info = $this->getMetaData($resource);
            if ($info['timed_out']) {
                $this->disconnect($resource);

                throw new TimeoutErrorException('Превышен таймаут %d секунд.', $this->config->getTimeout());
            }
        }
    }

    /**
     * Отправляет запрос
     *
     * @param resource $resource
     */
    private function sendRequest($resource, RequestInterface $request): void
    {
        $payload = $request->getMethod() . ' ' . $request->getUri()->getPath()
            . ($request->getUri()->getQuery() ? '?' . $request->getUri()->getQuery() : '')
            . ' HTTP/' . $request->getProtocolVersion() . "\r\n";

        /**
         * @var HeaderInterface $header
         */
        foreach ($request->getHeaders() as $header) {
            $payload .= $header->getLine() . "\r\n";
        }
        $payload .= "\r\n";

        fwrite($resource, $payload);
        if ($request->getBody()->has()) {
            fwrite($resource, $request->getBody()->get());
        }
    }

    /**
     * Разъединение
     *
     * @param resource|null $resource
     */
    private function disconnect($resource): void
    {
        if ($resource) {
            fclose($resource);
        }
    }

    /**
     * Соединение
     *
     * @return resource
     *
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    private function connect(UriInterface $uri)
    {
        $options = [];
        $context = $this->createContext($options);

        $address = 'tcp://';
        if ($uri->getScheme() === 'https') {
            $address = 'ssl://';
        }
        $address .= $uri->getHost();
        $port = $uri->getPort();
        if (!is_null($port)) {
            $address .= ':' . $port;
        }

        $resource = @stream_socket_client(
            $address,
            $errorCode,
            $errorMessage,
            $this->config->getTimeout(),
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($resource === false) {
            throw new ConnectionErrorException($errorMessage, $errorCode);
        }

        return $resource;
    }

    /**
     * Создать контекст
     *
     * @param string[][] $options
     *
     * @return resource
     */
    private function createContext(array $options)
    {
        if ($this->config->getSslVerify() === false) {
            $options['ssl']['verify_peer_name'] = false;
            $options['ssl']['verify_peer'] = false;
            $options['ssl']['allow_self_signed'] = true;
        }

        return stream_context_create($options);
    }
}
