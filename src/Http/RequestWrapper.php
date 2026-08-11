<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;

/**
 * @todo Only used for getPathSegments. Maybe the class should be
 * deleted.
 */
final class RequestWrapper
{
    /**
     * A Path Segment is defined as any part of the Request Target
     * (origin-form of the composed URI) that is between two slashes,
     * or the last part after the last slash.
     * 
     * The returned array is of length 1 at the very least, or an
     * UnexpectedValueException is thrown.
     * 
     * @todo Make not static? It would be more OOP.
     * 
     * @return array<string>
     */
    public static function getPathSegments(string $requestTarget): array
    {
        $parts = array_map(fn ($e) => urldecode($e), explode('/', $requestTarget));
        if (count($parts) === 0) {
            throw new UnexpectedValueException('Path should must at least one segment.');
        }
        return $parts;
    }
}