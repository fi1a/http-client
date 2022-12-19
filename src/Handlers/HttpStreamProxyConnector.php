<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;

use const STREAM_CLIENT_CONNECT;

/**
 * Http stream proxy
 */
class HttpStreamProxyConnector extends AbstractStreamProxyConnector
{
    private const STREAM_READ_LENGTH = 32768;

    /**
     * @inheritDoc
     */
    public function connect()
    {
        $address = 'tcp://' . $this->proxy->getHost() . ':' . $this->proxy->getPort();

        $resource = @stream_socket_client(
            $address,
            $errorCode,
            $errorMessage,
            $this->config->getTimeout(),
            STREAM_CLIENT_CONNECT,
            $this->context
        );

        if ($resource === false) {
            throw new ConnectionErrorException($errorMessage, $errorCode);
        }

        $uri = $this->request->getUri();
        $userInfo = $uri->getUserInfo();
        $port = $uri->getPort();
        if (!$port) {
            // @codeCoverageIgnoreStart
            $port = 80;
            if ($uri->getScheme() === 'https') {
                $port = 443;
            }
            // @codeCoverageIgnoreEnd
        }

        $connect = ($userInfo ? $userInfo . '@' : '') . $uri->getHost() . ':' . $port;

        $payload = 'CONNECT ' . $connect . ' HTTP/' . $this->request->getProtocolVersion() . "\r\n";
        $payload .= 'Host: ' . $connect . "\r\n";
        $username = $this->proxy->getUserName();
        if ($username) {
            $credentials = $username . ':' . (string) $this->proxy->getPassword();
            $payload .= 'Proxy-Authorization: Basic ' . base64_encode($credentials) . "\r\n";
        }
        $payload .= 'Proxy-Connection: close' . "\r\n";
        $payload .= "\r\n";

        fwrite($resource, $payload);

        while (!feof($resource)) {
            $headerLine = $this->readContentLine($resource, self::STREAM_READ_LENGTH);
            if ($headerLine === "\r\n") {
                break;
            }

            if ($headerLine === false) {
                throw new ConnectionErrorException('Ошибка при чтении потока');
            }

            /**
             * @psalm-suppress PossiblyFalseArgument
             */
            if (preg_match('#^HTTP/(\S+) (\d+) (.+)\r\n$#', $headerLine, $httpVersionAndStatus)) {
                $this->response->withStatus((int) $httpVersionAndStatus[2], $httpVersionAndStatus[3]);
                $this->response->withProtocolVersion(trim($httpVersionAndStatus[1]));

                continue;
            }

            /**
             * @psalm-suppress PossiblyFalseArgument
             */
            [$headerName, $headerValue] = array_map('trim', explode(':', $headerLine, 2));
            $this->response->withHeader($headerName, $headerValue);
        }

        if ($this->response->getStatusCode() === 407) {
            throw new ConnectionErrorException('Необходима аутентификация прокси');
        }

        return $resource;
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
}
