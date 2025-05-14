<?php

declare(strict_types=1);

namespace Osirisgate\Component\HttpClient\Request;

use Osirisgate\Core\Exception\RuntimeException;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';

    public static function fromString(string $method): self
    {
        return match (trim(strtoupper($method))) {
            'GET' => self::GET,
            'POST' => self::POST,
            'PUT' => self::PUT,
            'PATCH' => self::PATCH,
            'DELETE' => self::DELETE,
            default => throw new RuntimeException([
                'message' => 'unsupported.http.method',
                'details' => [
                    'method' => $method,
                ],
            ]),
        };
    }
}
