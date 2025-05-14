# 🌐 Osirisgate HTTP Client

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E=8.2-8892BF.svg)](https://www.php.net/releases/8.2/)

> Lightweight, elegant HTTP client built with modern PHP. Provides an expressive and extensible wrapper to perform REST API calls using clean, typed interfaces. Aligned with Osirisgate Core standards.

---

## 📦 Installation

```bash
composer require osirisgate/http-client
```

## ✅ Requirements

* PHP 8.2 or higher
* [osirisgate/core](https://github.com/osirisgate/core/)

## ✨ Features

* Simple and expressive methods for HTTP verbs: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`
* Structured exception system implementing `ExceptionInterface`
* Response abstraction through `ResponseInterface`
* Optional support for:
    * Query parameters (`filters`)
    * Custom headers
    * Low-level request options


## 🧑‍💻 How to use it ?

### ✅ Send a GET request

```php
use Osirisgate\Component\HttpClient\Request\Http;

$response = Http::get('https://jsonplaceholder.typicode.com/posts');
$data = $response->toArray();
```

### 📌 With query parameters

```php
$response = Http::get(
    'https://jsonplaceholder.typicode.com/comments',
    filters: ['postId' => 1]
);

$comments = $response->toArray();
```

### ✍️ Create a resource (POST)

```php
$response = Http::post(
    'https://jsonplaceholder.typicode.com/posts',
    payload: [
        'title' => 'foo',
        'body' => 'bar',
        'userId' => 1
    ]
);
```

### 🔁 Update a resource (PUT / PATCH)

```php
// PUT (Full update)
$response = Http::put('https://jsonplaceholder.typicode.com/posts/1', payload: [
    'id' => 1,
    'title' => 'new title',
    'body' => 'updated content',
    'userId' => 1
]);

// PATCH (Partial update)
$response = Http::patch('https://jsonplaceholder.typicode.com/posts/1', payload: [
    'title' => 'patched title'
]);
```

### ❌ Delete a resource

```php
$response = Http::delete('https://jsonplaceholder.typicode.com/posts/1');
```

## ⚙️ Advanced Configuration

### 🧾 Custom headers

```php
$response = Http::get(
    'https://api.example.com/data',
    headers: [
        'Authorization' => 'Bearer TOKEN',
        'X-Custom-Header' => 'value'
    ]
);
```

### 🛠 Request options

The options parameter allows for fine-grained control over the underlying HTTP request. Here's a detailed overview of the available options:

* `timeout`: int (default: 30) - Sets the timeout of the request in seconds. 
* `max_redirects`: int (default: 20) - Specifies the maximum number of redirects to follow. 
* `http_version`: string|null (default: null) - Sets the HTTP protocol version (e.g., '1.1', '2.0'). 
* `base_uri`: string|null (default: null) - Sets a base URI for resolving relative URLs. 
* `headers`: array (default: []) - An associative array of HTTP headers. 
* `auth_basic`: array|null (default: null) - Enables HTTP basic authentication with ['username', 'password']. 
* `auth_bearer`: string|null (default: null) - Sets the Authorization header with a bearer token. 
* `auth_ntlm`: array|null (default: null) - Enables NTLM authentication with ['username', 'password']. 
* `query`: array (default: []) - Associative array of query parameters to append to the URI. 
* `body`: resource|string|null (default: null) - The raw request body. 
* `json`: mixed|null (default: null) - If provided, will be JSON-encoded and used as the request body with the correct Content-Type. 
* `user_data`: mixed|null (default: null) - Arbitrary user-defined data associated with the request. 
* `bindto`: string|null (default: null) - Binds to a specific network interface or local port. 
* `proxy`: string|null (default: null) - URI of a proxy server to use. 
* `no_proxy`: string|null (default: null) - Comma-separated list of hostnames to bypass the proxy. 
* `verify_peer`: bool (default: true) - Whether to verify the peer's SSL certificate. Set to false with caution in production. 
* `verify_host`: bool (default: true) - Whether to verify the hostname against the SSL certificate. 
* `cafile`: string|null (default: null) - Path to a custom CA bundle file for SSL verification. 
* `capath`: string|null (default: null) - Path to a directory containing CA certificates. 
* `local_cert`: string|null (default: null) - Path to a client-side certificate file for client authentication. 
* `local_pk`: string|null (default: null) - Path to the private key file associated with local_cert. 
* `passphrase`: string|null (default: null) - Passphrase for the private key. 
* `ciphers`: string|null (default: null) - List of SSL/TLS ciphers to use. 
* `peer_fingerprint`: array|null (default: null) - Verify the peer's certificate by its fingerprint (e.g., ['sha256' => '...]). 
* `capture_peer_cert_chain`: bool (default: false) - Capture the peer's certificate chain for inspection via getInfo(). 
* `extra`: array (default: []) - Array of extra options to pass directly to the underlying HTTP client.

```php
$response = Http::get(
    'https://api.example.com/data',
    options: [
        'timeout' => 5,
        'verify_peer' => false
    ]
);
```

## 🚨 Error Handling

Any HTTP client or server error will throw a typed exception implementing `ExceptionInterface`.

```php
use Osirisgate\Core\Exception\ExceptionInterface;
use Osirisgate\Component\HttpClient\Request\Http;

try {
    $response = Http::get('https://api.example.com/invalid');
} catch (ExceptionInterface $e) {
    echo $e->getMessage(); // Not Found
    echo $e->getCode();    // 404
    print_r($e->format());
}
```

## 📄 Response Interface

Each request returns a structured object implementing `ResponseInterface`.
```php
Get specific field from response
  * ex: $value = $response->get('user.firstname') // return user firstname value or null if not found
  * ex: $value = $response->get('user.account.balance') // return user account balance value or null if not found
```

| Method                                          | Description                                                           |
|-------------------------------------------------|-----------------------------------------------------------------------|
| `getData()`                                     | Returns data if 'data' key is present. Otherwise, returns empty array.|
| `get(string $fieldName, mixed $default = null)` | Returns the value of a specific field, otherwise returns `$default`.  |
| `getStatusCode()`                               | Returns the HTTP status code                                          |
| `getHeaders()`                                  | Returns an array of headers                                           |
| `getContent()`                                  | Returns raw response body (string)                                    |
| `toArray()`                                     | Returns JSON-decoded response                                         |
| `cancel()`                                      | Closes the response stream and all related buffers.                   |
| `getInfo(?string $type = null)`                 | Returns meta information (e.g. url)                                   |

## 🧪 Run Tests

A full suite of unit tests is included and runs against a public API (`jsonplaceholder.typicode.com`).

```bash
vendor/bin/phpunit tests
```

## 🧰 Developer Tools

### ✅ Code style

```bash
composer run phpcs-fix
```

### 🧠 Static analysis

```bash
composer run phpstan
```

Or run both checks at once:

```bash
make check-code-quality
```

## 📜 License

This package is licensed under the [MIT License](LICENSE).

## 👤 Author

**Ulrich Geraud AHOGLA** Software Engineer — Osirisgate

If you have any questions or suggestions, please contact me via [developer@osirisgate.com](mailto:developer@osirisgate.com)

