<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Handlers;

use const CURLAUTH_BASIC;
use const CURLOPT_PROXY;
use const CURLOPT_PROXYAUTH;
use const CURLOPT_PROXYTYPE;
use const CURLOPT_PROXYUSERPWD;
use const CURLPROXY_SOCKS5;

/**
 * Socks5 curl proxy
 */
class Socks5CurlProxyConfigurator extends AbstractCurlProxyConfigurator
{
    /**
     * @inheritDoc
     */
    public function configure($resource): void
    {
        $options = [
            CURLOPT_PROXY => $this->proxy->getHost() . ':' . $this->proxy->getPort(),
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
        ];

        $username = $this->proxy->getUserName();
        $password = (string) $this->proxy->getPassword();
        if ($username) {
            $options[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
            $options[CURLOPT_PROXYUSERPWD] = $username . ':' . $password;
        }

        curl_setopt_array($resource, $options);
    }
}
