<?php

declare(strict_types=1);

namespace Osirisgate\Component\HttpClient\Util;

use Osirisgate\Component\HttpClient\Request\Exception\HttpException;
use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\ExceptionInterface;

abstract class HttpExceptionUtil
{
    public static function throw(\Throwable $exception): ExceptionInterface
    {
        $statusCode = $exception->getCode();
        if ($statusCode === 0) {
            $statusCode = StatusCode::INTERNAL_SERVER_ERROR->value;
        }

        return new HttpException([
            'status_code' => StatusCode::fromValue($statusCode),
            'message' => self::getReasonPhrase($statusCode),
            'details' => [
                'error' => $exception->getMessage(),
            ] + ($exception instanceof ExceptionInterface ? $exception->getDetails() : []),
        ]);
    }

    public static function getReasonPhrase(int $status): string
    {
        $statusTexts = StatusCode::statusTexts();

        return $statusTexts[$status] ?? $statusTexts[StatusCode::INTERNAL_SERVER_ERROR->value];
    }
}
