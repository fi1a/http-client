<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * Тело
 */
interface BodyInterface
{
    /**
     * Устанавливаем content type
     *
     * @return $this
     */
    public function setContentType(?string $mime = null);

    /**
     * Content type
     */
    public function getContentType(): ?string;

    /**
     * Возвращает размер тела
     */
    public function getSize(): int;
}
