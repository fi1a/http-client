<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;
use Fi1a\HttpClient\HttpInterface;
use Fi1a\HttpClient\RequestInterface;
use Fi1a\HttpClient\ResponseInterface;
use UnexpectedValueException;

use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_FAILONERROR;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HEADER;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_HTTPGET;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_HTTP_VERSION;
use const CURLOPT_MAXREDIRS;
use const CURLOPT_NOBODY;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_URL;
use const CURL_HTTP_VERSION_1_0;
use const CURL_HTTP_VERSION_1_1;
use const CURL_HTTP_VERSION_2_0;

/**
 * Curl-обработчик запросов
 */
class CurlHandler extends AbstractHandler
{
    /**
     * @inheritDoc
     */
    public function send(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $resource = $this->createHandler();
        $this->configure($resource);
        $this->configureByConfig($resource);
        $this->configureByRequest($resource, $request);
        $this->headers($resource, $response);
        $body = $this->getBody($resource);
        $body = $this->decompress($body, $response);
        $this->setBody($body, $response);
        $this->closeHandler($resource);

        return $response;
    }

    /**
     * Тело ответа
     *
     * @param resource $resource
     */
    private function getBody($resource): string
    {
        $rawBody = curl_exec($resource);
        if (curl_errno($resource) || is_bool($rawBody)) {
            throw new ConnectionErrorException(curl_error($resource));
        }

        return $rawBody;
    }

    /**
     * Чтение заголовков
     *
     * @param resource $resource
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @psalm-suppress UnusedClosureParam
     * @psalm-suppress MissingClosureParamType
     */
    private function headers($resource, ResponseInterface $response): void
    {
        $endHeaders = false;
        curl_setopt(
            $resource,
            CURLOPT_HEADERFUNCTION,
            function ($curl, string $headerLine) use ($response, $endHeaders) {
                if ($headerLine === "\r\n") {
                    $endHeaders = true;
                }

                if (!$endHeaders) {
                    /**
                     * @psalm-suppress PossiblyFalseArgument
                     */
                    if (preg_match('#^HTTP/(\S+) (\d+) (.+)\r\n$#', $headerLine, $httpVersionAndStatus)) {
                        $response->withStatus((int) $httpVersionAndStatus[2], $httpVersionAndStatus[3]);
                        $response->withProtocolVersion(trim($httpVersionAndStatus[1]));

                        return mb_strlen($headerLine);
                    }

                    /**
                     * @psalm-suppress PossiblyFalseArgument
                     */
                    [$headerName, $headerValue] = array_map('trim', explode(':', $headerLine, 2));
                    $response->withHeader($headerName, $headerValue);
                }

                return mb_strlen($headerLine);
            }
        );
    }

    /**
     * Создает обработчик
     *
     * @return resource
     */
    private function createHandler()
    {
        $resource =  $this->curlInit();

        if ($resource === false) {
            throw new ConnectionErrorException('Не удалось создать новый Curl обработчик');
        }

        return $resource;
    }

    /**
     * Создает Curl ресурс
     *
     * @return false|resource
     */
    protected function curlInit()
    {
        return curl_init();
    }

    /**
     * Закрывает обработчик
     *
     * @param resource|null $resource
     */
    private function closeHandler($resource): void
    {
        if ($resource) {
            curl_close($resource);
        }
    }

    /**
     * Установка опций
     *
     * @param resource $resource
     */
    private function configure($resource): void
    {
        $options = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => false,
        ];

        curl_setopt_array($resource, $options);
    }

    /**
     * Установка опций по конфигурации
     *
     * @param resource $resource
     */
    private function configureByConfig($resource): void
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER => $this->config->getSslVerify() ? 1 : 0,
            CURLOPT_SSL_VERIFYHOST => $this->config->getSslVerify() ? 1 : 0,
        ];

        if ($this->config->getTimeout() > 0) {
            $options[CURLOPT_TIMEOUT] = $this->config->getTimeout();
        }

        $options[CURLOPT_FOLLOWLOCATION] = $this->config->getAllowRedirects() ? 1 : 0;
        $options[CURLOPT_MAXREDIRS] = $this->config->getAllowRedirects() ? $this->config->getMaxRedirects() : 0;

        curl_setopt_array($resource, $options);
    }

    /**
     * Установка опций по запросу
     *
     * @param resource $resource
     */
    private function configureByRequest($resource, RequestInterface $request): void
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL => $request->getUri()->getUri(),
            CURLOPT_HTTPHEADER => $request->getHeaders()->getLine(),
        ];

        $protocolVersion = $this->getProtocolVersion($request->getProtocolVersion());
        if ($protocolVersion) {
            $options[CURLOPT_HTTP_VERSION] = $protocolVersion;
        }

        switch ($request->getMethod()) {
            case HttpInterface::HEAD:
                $options[CURLOPT_NOBODY] = true;

                break;
            case HttpInterface::GET:
                $options[CURLOPT_HTTPGET] = true;

                break;
            case HttpInterface::POST:
            case HttpInterface::PUT:
            case HttpInterface::DELETE:
            case HttpInterface::PATCH:
            case HttpInterface::OPTIONS:
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $request->getBody()->get();

                break;
        }

        curl_setopt_array($resource, $options);
    }

    /**
     * Возвращает версию HTTP протокола для Curl
     */
    private function getProtocolVersion(string $protocolVersion): int
    {
        switch ($protocolVersion) {
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
            case '2.0':
                if ($this->isSupportHttp20()) {
                    return CURL_HTTP_VERSION_2_0;
                }

                throw new UnexpectedValueException('HTTP 2.0 не поддерживается');
            default:
                return 0;
        }
    }

    /**
     * Поддерживается HTTP 2.0 или нет
     */
    protected function isSupportHttp20(): bool
    {
        return defined('CURL_HTTP_VERSION_2_0');
    }
}
