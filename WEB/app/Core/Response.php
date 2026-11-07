<?php
/**
 * Response Helper
 * CIS324 Real Estate Portal
 */

class Response
{
    /**
     * Send JSON response
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send success JSON
     */
    public static function success(array $data = [], string $message = 'Success', int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Send error JSON
     */
    public static function error(string $message, int $status = 400, array $errors = []): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Send validation error JSON
     */
    public static function validationError(array $errors): void
    {
        self::error('Validation failed', 422, $errors);
    }

    /**
     * Send not found JSON
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send unauthorized JSON
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send forbidden JSON
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Send server error JSON
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }

    /**
     * Redirect
     */
    public static function redirect(string $url, int $status = 302): void
    {
        header("Location: {$url}", true, $status);
        exit;
    }

    /**
     * Set cookie
     */
    public static function cookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true,
        string $samesite = 'Strict'
    ): void {
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ]);
    }

    /**
     * Delete cookie
     */
    public static function deleteCookie(string $name, string $path = '/', string $domain = ''): void
    {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => $path,
            'domain' => $domain,
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    /**
     * Set security headers
     */
    public static function setSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // CSP - adjust based on your needs
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://cdn.jsdelivr.net; " .
               "connect-src 'self';";
        header("Content-Security-Policy: {$csp}");
    }

    /**
     * Send file download
     */
    public static function download(string $filePath, string $name = ''): void
    {
        if (!file_exists($filePath)) {
            self::notFound('File not found');
        }

        $name = $name ?: basename($filePath);
        $size = filesize($filePath);
        $mime = mime_content_type($filePath);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . $size);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        readfile($filePath);
        exit;
    }
}