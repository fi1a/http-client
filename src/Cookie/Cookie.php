<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Http\Cookie as HttpCookie;

use function preg_match;

use const FILTER_VALIDATE_IP;

/**
 * Cookie
 */
class Cookie extends HttpCookie implements CookieInterface
{
    /**
     * @var string[]
     */
    private static $cookieKeys = [
        'Name', 'Value', 'Domain', 'Path', 'Expires', 'Max-Age', 'Secure', 'HttpOnly',
    ];

    /**
     * @var string[]
     */
    protected $modelKeys = [
        'Name', 'Value', 'Domain', 'Path', 'Expires', 'Max-Age', 'Secure', 'HttpOnly', 'Session',
    ];

    /**
     * @inheritDoc
     */
    protected function getDefaultModelValues()
    {
        return array_merge(parent::getDefaultModelValues(), [
            'Session' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSession(): bool
    {
        return (bool) $this->modelGet('Session');
    }

    /**
     * @inheritDoc
     */
    public function setSession(bool $secure)
    {
        $this->modelSet('Session', $secure);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function matchDomain(string $domain): bool
    {
        $cookieDomain = $this->getDomain();
        if (is_null($cookieDomain)) {
            return true;
        }

        if (!$domain) {
            return false;
        }

        /**
         * @var string $cookieDomain
         */
        $cookieDomain = mb_strtolower(ltrim($cookieDomain, '.'));
        /**
         * @var string $domain
         */
        $domain = mb_strtolower($domain);

        if ($cookieDomain === '' || $domain === $cookieDomain) {
            return true;
        }

        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) preg_match('/\.' . preg_quote($cookieDomain, '/') . '$/', $domain);
    }

    /**
     * @inheritDoc
     */
    public function matchPath(string $path): bool
    {
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        $cookiePath = $this->getPath();
        if ($cookiePath !== '/') {
            $cookiePath = rtrim($cookiePath, '/');
        }

        if ($cookiePath === '/' || $cookiePath === $path) {
            return true;
        }

        if (mb_strpos($path, $cookiePath) !== 0) {
            return false;
        }

        return mb_substr($path, mb_strlen($cookiePath), 1) === '/';
    }

    /**
     * @inheritDoc
     */
    public static function fromString(string $string)
    {
        $parts = array_filter(array_map('trim', explode(';', $string)));

        if (!count($parts)) {
            return new static();
        }

        $cookie = [];

        foreach ($parts as $part) {
            $cookieParts = explode('=', $part, 2);
            $key = trim($cookieParts[0]);
            $value = isset($cookieParts[1])
                ? trim($cookieParts[1], " \n\r\t\0\x0B")
                : true;

            if (!in_array($key, static::$cookieKeys)) {
                if (!isset($cookie['Name'])) {
                    $cookie['Name'] = $key;
                    $cookie['Value'] = rawurldecode((string) $value);
                }

                continue;
            }

            if (in_array($key, ['Max-Age', 'Expires'])) {
                $value = (int) $value;
            }

            $cookie[$key] = $value;
        }

        return new static($cookie);
    }
}
