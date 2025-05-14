<?php

declare(strict_types=1);

namespace Osirisgate\Component\HttpClient\Response;

use Osirisgate\Core\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;

interface ResponseInterface extends SymfonyResponseInterface
{
    public static function from(SymfonyResponseInterface $response): self;

    /**
     * @throws ExceptionInterface
     */
    public function getStatusCode(): int;

    /**
     * @return string[][]
     *
     * @throws ExceptionInterface
     */
    public function getHeaders(bool $throw = true): array;

    /**
     * @throws ExceptionInterface
     */
    public function getContent(bool $throw = true): string;

    /**
     * @return array<string, mixed>
     *
     * @throws ExceptionInterface
     */
    public function toArray(bool $throw = true): array;

    public function cancel(): void;

    public function getInfo(?string $type = null): mixed;

    /**
     * Get the response data.
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Get specific field from response
     * ex: $value = $response->get('user.firstname') // return user firstname value or null if not found
     * ex: $value = $response->get('user.account.balance') // return user account balance value or null if not found.
     */
    public function get(string $fieldName, mixed $default = null): mixed;
}
