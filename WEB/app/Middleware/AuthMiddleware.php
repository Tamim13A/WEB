<?php
/**
 * Authentication Middleware
 * CIS324 Real Estate Portal
 */

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please login to access this page');
            Response::redirect('/login');
            return false;
        }
        
        // Check session timeout
        if (!Session::checkTimeout()) {
            Session::flash('error', 'Session expired. Please login again');
            Response::redirect('/login');
            return false;
        }
        
        return true;
    }
}