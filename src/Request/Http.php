<?php

declare(strict_types=1);

namespace Osirisgate\Component\HttpClient\Request;

use Osirisgate\Component\HttpClient\Request\Exception\HttpException;
use Osirisgate\Component\HttpClient\Response\Response;
use Osirisgate\Component\HttpClient\Response\ResponseInterface;
use Osirisgate\Component\HttpClient\Util\HttpExceptionUtil;
use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\ExceptionInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class Http
{
    /**
     * Make an HTTP GET request.
     *
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ExceptionInterface
     */
    public static function get(string $url, array $headers = [], array $filters = [], array $options = []): ResponseInterface
    {
        return self::request(HttpMethod::GET, $url, $headers, [], $filters, $options);
    }

    /**
     * Make an HTTP POST request.
     *
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $options
     *
     * @throws HttpException
     * @throws ExceptionInterface
     */
    public static function post(string $url, array $headers = [], array $payload = [], array $filters = [], array $options = []): ResponseInterface
    {
        return self::request(HttpMethod::POST, $url, $headers, $payload, $filters, $options);
    }

    /**
     * Make an HTTP PUT request.
     *
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ExceptionInterface
     */
    public static function put(string $url, array $headers = [], array $payload = [], array $filters = [], array $options = []): ResponseInterface
    {
        return self::request(HttpMethod::PUT, $url, $headers, $payload, $filters, $options);
    }

    /**
     * Make an HTTP PATCH request.
     *
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ExceptionInterface
     */
    public static function patch(string $url, array $headers = [], array $payload = [], array $filters = [], array $options = []): ResponseInterface
    {
        return self::request(HttpMethod::PATCH, $url, $headers, $payload, $filters, $options);
    }

    /**
     * Make an HTTP DELETE request.
     *
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $options
     *
     * @throws HttpException
     * @throws ExceptionInterface
     */
    public static function delete(string $url, array $headers = [], array $payload = [], array $filters = [], array $options = []): ResponseInterface
    {
        return self::request(HttpMethod::DELETE, $url, $headers, $payload, $filters, $options);
    }

    /**
     * Make an HTTP request.
     *
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $options
     *
     * @throws HttpException
     * @throws ExceptionInterface
     */
    private static function request(HttpMethod $method, string $url, array $headers = [], array $payload = [], array $filters = [], array $options = []): ResponseInterface
    {
        $method = $method->value;
        $client = new RetryableHttpClient(HttpClient::create());

        /** @var array<string, mixed> $mergedHeaders */
        $mergedHeaders = self::mergeHeaders($headers);
        /** @var array<string, mixed> $optionsHeaders */
        $optionsHeaders = $options['headers'] ?? [];

        $options['headers'] = array_merge($mergedHeaders, $optionsHeaders);

        if (count($filters) > 0) {
            /** @var array<string, mixed> $optionQuery */
            $optionQuery = $options['query'] ?? [];
            $options['query'] = array_merge($filters, $optionQuery);
        }

        if (in_array(strtoupper($method), [HttpMethod::POST->value, HttpMethod::PUT->value, HttpMethod::PATCH->value], true)) {
            $options['json'] = $payload;
        }

        $options = array_merge(self::getDefaultOptions(), $options);

        try {
            $response = $client->withOptions($options)->request(strtoupper($method), $url, $options);
            $status = $response->getStatusCode();

            if ($status >= StatusCode::BAD_REQUEST->value) {
                throw new HttpException([
                    'status_code' => StatusCode::tryFrom($status),
                    'message' => HttpExceptionUtil::getReasonPhrase($status),
                    'details' => json_decode($response->getContent(false), true),
                ]);
            }

            return Response::from($response);
        } catch (ExceptionInterface | HttpExceptionInterface | TransportExceptionInterface $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }

    /**
     * @param array<string, mixed> $headers
     *
     * @return array<string, mixed>
     */
    private static function mergeHeaders(array $headers): array
    {
        return array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);
    }

    /**
     * Default options.
     *
     * @return array<string, mixed>
     */
    private static function getDefaultOptions(): array
    {
        return [
            'timeout' => 30,
            'max_redirects' => 20,
            'http_version' => null, // e.g. '1.1' or '2.0'
            'base_uri' => null,
            'headers' => [],
            'auth_basic' => null,   // ['username', 'password']
            'auth_bearer' => null,  // JWT token string
            'auth_ntlm' => null,    // ['username', 'password']
            'query' => [],
            'body' => null,
            'json' => null,
            'user_data' => null,
            'bindto' => null,
            'proxy' => null,
            'no_proxy' => null,
            'verify_peer' => true,
            'verify_host' => true,
            'cafile' => null,
            'capath' => null,
            'local_cert' => null,
            'local_pk' => null,
            'passphrase' => null,
            'ciphers' => null,
            'peer_fingerprint' => null,
            'capture_peer_cert_chain' => false,
            'extra' => [],
        ];
    }
}
