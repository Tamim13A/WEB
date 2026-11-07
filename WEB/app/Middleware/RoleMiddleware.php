<?php
/**
 * Role-based Access Control Middleware
 * CIS324 Real Estate Portal
 */

namespace App\Middleware;

class RoleMiddleware
{
    private string|array $roles;

    public function __construct(string|array $roles = [])
    {
        $this->roles = $roles;
    }

    /**
     * Handle middleware with roles from route attribute
     * Usage: ['middleware' => 'role:admin,agent']
     */
    public function handle(string $rolesParam = ''): bool
    {
        $roles = $this->roles;
        
        if ($rolesParam !== '') {
            $roles = array_map('trim', explode(',', $rolesParam));
        }
        
        if (empty($roles)) {
            return true;
        }

        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please login to access this page');
            Response::redirect('/login');
            return false;
        }

        $userRole = Session::userRole();
        
        if (!in_array($userRole, (array)$roles, true)) {
            Session::flash('error', 'You do not have permission to access this page');
            Response::redirect('/');
            return false;
        }

        return true;
    }
}