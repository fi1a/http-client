<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use LogicException;

/**
 * Хранилище кук
 */
class CookieStorage implements CookieStorageInterface
{
    /**
     * @var CookieCollectionInterface
     */
    protected $cookies;

    public function __construct()
    {
        $this->cookies = new CookieCollection();
    }

    /**
     * @inheritDoc
     */
    public function addCookie(CookieInterface $cookie): bool
    {
        try {
            $cookie->validate();
        } catch (LogicException $exception) {
            return false;
        }

        foreach ($this->cookies->getArrayCopy() as $index => $itemCookie) {
            assert($itemCookie instanceof CookieInterface);
            if (
                $itemCookie->getPath() !== $cookie->getPath()
                || $itemCookie->getDomain() !== $cookie->getDomain()
                || $itemCookie->getName() !== $cookie->getName()
            ) {
                continue;
            }

            if (
                !$cookie->getSession() && $itemCookie->getSession()
                || $cookie->getSession() && !$itemCookie->getSession()
                || $cookie->getExpires() > $itemCookie->getExpires()
                || $cookie->getValue() !== $itemCookie->getValue()
            ) {
                $this->cookies->delete($index);

                continue;
            }

            return false;
        }

        $this->cookies[] = $cookie;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteCookie(string $name, ?string $domain = null, ?string $path = null): void
    {
        /**
         * @var CookieCollectionInterface $cookies
         */
        $cookies = $this->cookies->filter(function (CookieInterface $cookie) use ($name, $domain, $path) {
            if ($name !== $cookie->getName()) {
                return true;
            }
            if ($domain && !$cookie->matchDomain($domain)) {
                return true;
            }

            return $path && !$cookie->matchPath($path);
        });
        $this->cookies = $cookies;
    }

    /**
     * @inheritDoc
     */
    public function getCookiesWithCondition(
        string $domain,
        string $path,
        ?string $scheme = null
    ): CookieCollectionInterface {
        /**
         * @var CookieCollectionInterface $collection
         */
        $collection = $this->cookies->filter(function (CookieInterface $cookie) use ($domain, $path, $scheme) {
            return $cookie->matchDomain($domain)
                && $cookie->matchPath($path)
                && !$cookie->isExpired()
                && (is_null($scheme) || !$cookie->getSecure() || $scheme === 'https');
        });

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function getCookies(): CookieCollectionInterface
    {
        return $this->cookies;
    }
}
