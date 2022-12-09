<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

use InvalidArgumentException;

/**
 * Заголовок
 */
class Header implements HeaderInterface
{
    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $name;

    /**
     * @var string|null
     */
    private $value;

    public function __construct(string $name, ?string $value = null)
    {
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): void
    {
        if (!$name) {
            throw new InvalidArgumentException('Название заголовка не может быть пустым');
        }

        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getLine(): string
    {
        $value = $this->getValue();

        return $this->getName() . ': ' . ($value ?: '');
    }
}
