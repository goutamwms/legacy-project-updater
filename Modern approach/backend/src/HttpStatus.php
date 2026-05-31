<?php

declare(strict_types=1);

/**
 * HTTP status code constants.
 *
 * Centralises integer status codes so handlers never hard-code raw numbers.
 */
final class HttpStatus
{
    public const int OK                    = 200;
    public const int BAD_REQUEST           = 400;
    public const int NOT_FOUND             = 404;
    public const int METHOD_NOT_ALLOWED    = 405;
    public const int INTERNAL_SERVER_ERROR = 500;

    private function __construct()
    {
    }
}
