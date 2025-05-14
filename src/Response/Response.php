<?php

declare(strict_types=1);

namespace Osirisgate\Component\HttpClient\Response;

use Osirisgate\Component\HttpClient\Util\HttpExceptionUtil;
use Osirisgate\Core\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;

final readonly class Response implements ResponseInterface
{
    private function __construct(
        private SymfonyResponseInterface $response,
    ) {
    }

    public static function from(SymfonyResponseInterface $response): self
    {
        return new self($response);
    }

    /**
     * @throws ExceptionInterface
     */
    public function getStatusCode(): int
    {
        try {
            return $this->response->getStatusCode();
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }

    /**
     * @return string[][]
     *
     * @throws ExceptionInterface
     */
    public function getHeaders(bool $throw = true): array
    {
        try {
            return $this->response->getHeaders($throw);
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function getContent(bool $throw = true): string
    {
        try {
            return $this->response->getContent($throw);
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ExceptionInterface
     */
    public function toArray(bool $throw = true): array
    {
        try {
            return $this->response->toArray($throw);
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }

    public function cancel(): void
    {
        $this->response->cancel();
    }

    public function getInfo(?string $type = null): mixed
    {
        return $this->response->getInfo($type);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ExceptionInterface
     */
    public function getData(): array
    {
        try {
            /** @var array<string, mixed> $data */
            $data = $this->toArray()['data'] ?? [];

            return $data;
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function get(string $fieldName, mixed $default = null): mixed
    {
        try {
            $data = $this->toArray();
            foreach (explode('.', $fieldName) as $key) {
                if (!isset($data[$key])) {
                    return $default;
                }

                $data = $data[$key];
            }

            return $data;
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }
}
