<?php
/**
 * Authentication & Authorization (RBAC)
 * CIS324 Real Estate Portal
 */

class Auth
{
    /**
     * Check if user has specific role
     */
    public static function hasRole(string|array $roles): bool
    {
        if (!Session::isLoggedIn()) {
            return false;
        }
        
        $userRole = Session::userRole();
        $roles = is_array($roles) ? $roles : [$roles];
        
        return in_array($userRole, $roles, true);
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    /**
     * Check if user is agent
     */
    public static function isAgent(): bool
    {
        return self::hasRole('agent');
    }

    /**
     * Check if user is customer
     */
    public static function isCustomer(): bool
    {
        return self::hasRole('customer');
    }

    /**
     * Check if user is agent or admin
     */
    public static function isAgentOrAdmin(): bool
    {
        return self::hasRole(['agent', 'admin']);
    }

    /**
     * Check if user owns a resource
     */
    public static function owns(int $resourceUserId): bool
    {
        if (!Session::isLoggedIn()) {
            return false;
        }
        
        // Admin can access everything
        if (self::isAdmin()) {
            return true;
        }
        
        return Session::userId() === $resourceUserId;
    }

    /**
     * Require authentication (redirect if not logged in)
     */
    public static function requireAuth(string $redirect = '/login'): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please login to access this page');
            Response::redirect($redirect);
        }
        
        // Check session timeout
        if (!Session::checkTimeout()) {
            Session::flash('error', 'Session expired. Please login again');
            Response::redirect($redirect);
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole(string|array $roles, string $redirect = '/'): void
    {
        self::requireAuth();
        
        if (!self::hasRole($roles)) {
            Session::flash('error', 'You do not have permission to access this page');
            Response::redirect($redirect);
        }
    }

    /**
     * Require admin
     */
    public static function requireAdmin(string $redirect = '/'): void
    {
        self::requireRole('admin', $redirect);
    }

    /**
     * Require agent
     */
    public static function requireAgent(string $redirect = '/'): void
    {
        self::requireRole('agent', $redirect);
    }

    /**
     * Require ownership or admin
     */
    public static function requireOwnership(int $resourceUserId, string $redirect = '/'): void
    {
        self::requireAuth();
        
        if (!self::owns($resourceUserId)) {
            Session::flash('error', 'You do not have permission to access this resource');
            Response::redirect($redirect);
        }
    }

    /**
     * Get role-based dashboard route
     */
    public static function dashboardRoute(): string
    {
        $role = Session::userRole();
        return match ($role) {
            'admin' => '/admin/dashboard',
            'agent' => '/agent/dashboard',
            'customer' => '/customer/dashboard',
            default => '/',
        };
    }

    /**
     * Middleware: Check authentication for API
     */
    public static function apiAuth(): ?array
    {
        if (!Session::isLoggedIn()) {
            Response::unauthorized('Authentication required');
        }
        
        if (!Session::checkTimeout()) {
            Response::unauthorized('Session expired');
        }
        
        return Session::user();
    }

    /**
     * Middleware: Check role for API
     */
    public static function apiRole(string|array $roles): ?array
    {
        $user = self::apiAuth();
        
        if (!$user || !self::hasRole($roles)) {
            Response::forbidden('Insufficient permissions');
        }
        
        return $user;
    }
}