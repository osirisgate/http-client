<?php

declare(strict_types=1);

namespace Osirisgate\Component\HttpClient\Request\Exception;

use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\Exception;

final class HttpException extends Exception
{
    public function __construct(array $errors)
    {
        /** @var StatusCode $statusCode */
        $statusCode = $errors['status_code'] ?? StatusCode::INTERNAL_SERVER_ERROR;
        $this->statusCode = $statusCode;
        unset($errors['status_code']);
        parent::__construct($errors);
    }
}
