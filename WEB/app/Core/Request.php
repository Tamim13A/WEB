<?php
/**
 * Request Wrapper
 * CIS324 Real Estate Portal
 */

class Request
{
    private array $input = [];
    private array $query = [];
    private array $files = [];

    public function __construct()
    {
        $this->query = $_GET;
        $this->input = array_merge($_POST, json_decode(file_get_contents('php://input'), true) ?? []);
        $this->files = $_FILES;
    }

    /**
     * Get input value
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->input[$key] ?? $default;
    }

    /**
     * Get all input
     */
    public function all(): array
    {
        return $this->input;
    }

    /**
     * Get query parameter
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get all query parameters
     */
    public function queryAll(): array
    {
        return $this->query;
    }

    /**
     * Check if input has key
     */
    public function has(string $key): bool
    {
        return isset($this->input[$key]);
    }

    /**
     * Get uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Check if file was uploaded
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Get HTTP method
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        );
    }

    /**
     * Get full URL
     */
    public function fullUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Get base URL
     */
    public function baseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Get path
     */
    public function path(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    }

    /**
     * Get IP address
     */
    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get user agent
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }

    /**
     * Get header
     */
    public function header(string $name, string $default = ''): string
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$name] ?? $default;
    }

    /**
     * Get bearer token
     */
    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization');
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    /**
     * Get only specified keys
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->input, array_flip($keys));
    }

    /**
     * Get all except specified keys
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->input, array_flip($keys));
    }
}