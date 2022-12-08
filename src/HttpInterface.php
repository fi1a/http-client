<?php

declare(strict_types=1);

namespace Fi1a\HttpClient;

/**
 * HTTP
 */
interface HttpInterface
{
    public const HEAD = 'HEAD';
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const PATCH = 'PATCH';
    public const OPTIONS = 'OPTIONS';
    public const TRACE = 'TRACE';
}
