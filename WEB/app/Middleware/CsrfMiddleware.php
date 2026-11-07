<?php
/**
 * CSRF Protection Middleware
 * CIS324 Real Estate Portal
 */

namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(): bool
    {
        // Only check CSRF for state-changing methods
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        // Skip for API routes (they use token in header)
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        if (str_starts_with($uri, '/api/')) {
            return $this->validateApiToken();
        }

        // Get token from POST data or header
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!Security::validateCsrfToken($token)) {
            if (Request::isAjax()) {
                Response::error('Invalid CSRF token', 419);
            } else {
                Session::flash('error', 'Invalid form submission. Please try again.');
                Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
            return false;
        }

        return true;
    }

    /**
     * Validate CSRF token for API requests
     */
    private function validateApiToken(): bool
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (empty($token) || !Security::validateCsrfToken($token)) {
            Response::error('Invalid CSRF token', 419);
            return false;
        }
        
        return true;
    }
}