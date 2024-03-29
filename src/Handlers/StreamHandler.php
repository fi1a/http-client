<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\Http\HeaderInterface;
use Fi1a\Http\UriInterface;
use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;
use Fi1a\HttpClient\Handlers\Exceptions\ErrorException;
use Fi1a\HttpClient\Handlers\Exceptions\TimeoutErrorException;
use Fi1a\HttpClient\Proxy\ProxyInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;

use function idn_to_ascii;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;
use const STREAM_CLIENT_CONNECT;

/**
 * Stream-обработчик запросов
 */
class StreamHandler extends AbstractHandler
{
    private const STREAM_READ_LENGTH = 32768;

    /**
     * @var int
     */
    private $redirects = 0;

    /**
     * @var string|null
     */
    private $fromUrl;

    /**
     * @inheritDoc
     */
    public function send(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $resource = null;
        do {
            if ($resource) {
                $this->disconnect($resource);
            }
            $resource = $this->connect($request, $response);
            @stream_set_timeout($resource, $this->config->getTimeout());
            $this->sendRequest($resource, $request);
            $response = $this->getHeaders($resource, $response);
        } while ($this->redirect($request, $response));

        $body = $this->getBody($resource, $response);
        $body = $this->decompress($body, $response);
        $response = $this->setBody($body, $response);

        if ($this->isConnectionError($response)) {
            throw new ConnectionErrorException('Пустой ответ сервера');
        }

        $this->disconnect($resource);

        return $response;
    }

    /**
     * Проверяем возвращенные данные и если ничего не вернули выбрасываем исключение
     */
    protected function isConnectionError(ResponseInterface $response): bool
    {
        return $response->getHeaders()->count() === 0 && !$response->getBody()->has();
    }

    /**
     * Редиректы
     */
    private function redirect(RequestInterface &$request, ResponseInterface &$response): bool
    {
        if (!$this->config->getAllowRedirects()) {
            return false;
        }

        $headerLocation = $response->getLastHeader('Location');

        if (!$headerLocation || !$headerLocation->getValue()) {
            return false;
        }

        if (!$this->fromUrl) {
            $this->fromUrl = $request->getUri()->maskedUri();
        }

        if ($this->config->getMaxRedirects() !== 0 && $this->redirects >= $this->config->getMaxRedirects()) {
            throw new ErrorException(
                sprintf(
                    'Максимальное число редиректор %d было достигнуто при запросе %s',
                    $this->config->getMaxRedirects(),
                    $this->fromUrl
                )
            );
        }

        $request = $request->withUri($request->getUri()->replace((string) $headerLocation->getValue()));
        $headers = $response->getHeaders();
        /**
         * @var int $key
         */
        foreach ($headers as $key => $header) {
            assert($header instanceof HeaderInterface);
            if (mb_strtolower($header->getName()) !== 'set-cookie') {
                $headers->delete($key);
            }
        }
        $response = $response->withHeaders($headers);

        $this->redirects++;

        return true;
    }

    /**
     * Transfer-Encoding: chunked
     *
     * @param resource $resource
     */
    private function getBodyChunked($resource): string
    {
        $rawBody = '';

        while (!feof($resource)) {
            $line = $this->readContentLine($resource, self::STREAM_READ_LENGTH);
            if ($line === "\r\n" || $line === false) {
                continue;
            }
            $this->checkReadErrors($resource, $line);
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
    private function getBodyNoEncoding($resource, ?int $contentLength): string
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
    private function getBody($resource, ResponseInterface $response): string
    {
        $transferEncoding = $response->getLastHeader('Transfer-Encoding');
        if ($transferEncoding && $transferEncoding->getValue() === 'chunked') {
            return $this->getBodyChunked($resource);
        }

        $contentLength = null;
        $contentLengthHeader = $response->getLastHeader('Content-Length');
        if ($contentLengthHeader) {
            $contentLength = (int) $contentLengthHeader->getValue();
        }

        return $this->getBodyNoEncoding($resource, $contentLength);
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
    private function getHeaders($resource, ResponseInterface $response): ResponseInterface
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
                $response = $response->withStatus((int) $httpVersionAndStatus[2], $httpVersionAndStatus[3]);
                $response = $response->withProtocolVersion(trim($httpVersionAndStatus[1]));

                continue;
            }

            /**
             * @psalm-suppress PossiblyFalseArgument
             */
            [$headerName, $headerValue] = array_map('trim', explode(':', $headerLine, 2));
            $response = $response->withHeader($headerName, $headerValue);
        }

        return $response;
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

            throw new ConnectionErrorException('Ошибка при чтении потока');
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
        $path = $request->getUri()->path();
        if (!$path) {
            $path = '/';
        }
        $payload = $request->getMethod() . ' ' . $path
            . ($request->getUri()->query() ? '?' . $request->getUri()->query() : '')
            . ' HTTP/' . $this->getProtocolVersion($request->getProtocolVersion()) . "\r\n";

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
     * Соединение через прокси
     *
     * @param resource $context
     *
     * @return resource
     */
    protected function proxyConnect($context, RequestInterface $request, ResponseInterface $response)
    {
        return $this->factoryProxy($context, $request, $response)->connect();
    }

    /**
     * Вызывет фабричный метод для создания объекта proxy коннектора
     *
     * @param resource $context
     */
    protected function factoryProxy(
        $context,
        RequestInterface &$request,
        ResponseInterface &$response
    ): StreamProxyConnectorInterface {
        $proxy = $request->getProxy();
        assert($proxy instanceof ProxyInterface);

        return (new StreamProxyConnectorFactory())->factory(
            $context,
            $this->config,
            $request,
            $response,
            $proxy
        );
    }

    /**
     * Соединение
     *
     * @return resource
     */
    private function connect(RequestInterface $request, ResponseInterface $response)
    {
        $uri = $request->getUri();

        $options = [];

        $context = $this->createContext($options, $uri);

        if ($request->getProxy()) {
            return $this->proxyConnect($context, $request, $response);
        }

        $ip = filter_var(
            $uri->host(),
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6
        ) ? $uri->host() : gethostbyname($request->getUri()->host());

        $port = $uri->port();

        $address = 'tcp://';
        if ($uri->scheme() === 'https') {
            $address = 'ssl://';
            if (!$port) {
                $port = 443;
            }
        }
        if (!$port) {
            $port = 80;
        }

        $address .= $ip;
        $address .= ':' . $port;

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
    private function createContext(array $options, UriInterface $uri)
    {
        $options['ssl']['peer_name'] = idn_to_ascii($uri->host());

        if ($this->config->getSslVerify() === false) {
            $options['ssl']['verify_peer_name'] = false;
            $options['ssl']['verify_peer'] = false;
            $options['ssl']['allow_self_signed'] = true;
        }

        return stream_context_create($options);
    }

    /**
     * Возвращает версию протокола
     */
    private function getProtocolVersion(string $protocolVersion): string
    {
        if (in_array($protocolVersion, ['1.0', '1.1', '2.0'])) {
            return $protocolVersion;
        }

        return '1.0';
    }
}
