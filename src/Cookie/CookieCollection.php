<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Collection\AbstractInstanceCollection;

/**
 * Коллекция кук
 */
class CookieCollection extends AbstractInstanceCollection implements CookieCollectionInterface
{
    /**
     * @inheritDoc
     */
    protected function factory($key, $value)
    {
        return new Cookie((array) $value);
    }

    /**
     * @inheritDoc
     */
    protected function isInstance($value): bool
    {
        return $value instanceof CookieInterface;
    }

    /**
     * @inheritDoc
     */
    public function getByName(string $name)
    {
        foreach ($this->getArrayCopy() as $cookie) {
            assert($cookie instanceof CookieInterface);

            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getValid(): CookieCollectionInterface
    {
        return $this->filter(function (CookieInterface $cookie) {
            try {
                $cookie->validate();
            } catch (\LogicException $exception) {
                return false;
            }

            return true;
        });
    }
}
