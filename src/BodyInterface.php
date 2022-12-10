<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

interface BodyInterface
{
    /**
     * Устанавливаем content type
     *
     * @return $this
     */
    public function withContentType(?string $mime = null);

    /**
     * Content type
     */
    public function getContentType(): ?string;
}
