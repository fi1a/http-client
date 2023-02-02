<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use Fi1a\HttpClient\Handlers\Exceptions\ConnectionErrorException;

use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;
use const STREAM_CLIENT_CONNECT;
use const STREAM_CRYPTO_METHOD_ANY_CLIENT;

/**
 * Socks5 stream proxy
 *
 * @codeCoverageIgnore
 */
class Socks5StreamProxyConnector extends AbstractStreamProxyConnector
{
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

        $request = pack('C', 0x05);
        if ($this->proxy->getUserName() === null) {
            // one method, no authentication
            $request .= pack('C2', 0x01, 0x00);
        } else {
            // two methods, username/password and no authentication
            $request .= pack('C3', 0x02, 0x02, 0x00);
        }
        fwrite($resource, $request);
        $response = unpack('Cversion/Cmethod', fread($resource, 2));
        $username = $this->proxy->getUserName();
        if ($response['method'] === 0x02 && $username !== null) {
            $auth = pack('C2', 0x01, mb_strlen($username))
            . $username . pack('C', mb_strlen((string) $this->proxy->getPassword()))
            . (string) $this->proxy->getPassword();
            fwrite($resource, $auth);

            $response = unpack('Cversion/Cstatus', fread($resource, 2));

            if ($response['version'] !== 0x01 || $response['status'] !== 0x00) {
                throw new ConnectionErrorException('Необходима аутентификация прокси');
            }
            $port = $uri->port();
            if (!$port) {
                $port = 80;
                if ($uri->scheme() === 'https') {
                    $port = 443;
                }
            }

            $ip = @inet_pton($uri->host());

            $request = pack('C3', 0x05, 0x01, 0x00);

            if ($ip === false) {
                // not an IP, send as hostname
                $request .= pack('C2', 0x03, mb_strlen($uri->host())) . $uri->host();
            } else {
                // send as IPv4 / IPv6
                $request .= pack(
                    'C',
                    filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false
                        ? 0x01
                        : 0x04
                ) . $ip;
            }
            $request .= pack('n', $port);
            fwrite($resource, $request);

            $response = unpack('Cversion/Cstatus/Cnull/Ctype', fread($resource, 4));
            if ($response['version'] !== 0x05 || $response['status'] !== 0x00 || $response['null'] !== 0x00) {
                throw new ConnectionErrorException('Неверный ответ SOCKS');
            }
            // На основе типа адреса пропускаем
            if ($response['type'] === 0x01) {
                // IPv4
                fread($resource, 6);
            } elseif ($response['type'] === 0x03) {
                // domain name
                $length = unpack('Clength', fread($resource, 1));
                fread($resource, (int) $length['length'] + 2);
            } elseif ($response['type'] === 0x04) {
                // IPv6
                fread($resource, 18);
            } else {
                throw new ConnectionErrorException('Неверный ответ SOCKS: Ошибка в типе адреса');
            }
        } elseif ($response['method'] !== 0x00) {
            throw new ConnectionErrorException('Запрошен недопустимый метод аутентификации');
        }

        if ($uri->scheme() === 'https') {
            stream_socket_enable_crypto($resource, true, STREAM_CRYPTO_METHOD_ANY_CLIENT);
        }

        return $resource;
    }
}
