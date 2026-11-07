<?php
/**
 * Session Wrapper
 * CIS324 Real Estate Portal
 */

class Session
{
    private static bool $started = false;
    private static array $flash = [];

    /**
     * Start session with secure configuration
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = require __DIR__ . '/../Config/App.php';
        $sessionConfig = $config['session'] ?? [];

        // Secure session configuration
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        
        if ($sessionConfig['secure'] ?? false) {
            ini_set('session.cookie_secure', '1');
        }
        
        $samesite = $sessionConfig['samesite'] ?? 'Strict';
        if (PHP_VERSION_ID >= 70300) {
            ini_set('session.cookie_samesite', $samesite);
        }

        session_name($sessionConfig['name'] ?? 'realestate_session');
        
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'] ?? 1800,
            'path' => '/',
            'domain' => '',
            'secure' => $sessionConfig['secure'] ?? false,
            'httponly' => true,
            'samesite' => $samesite,
        ]);

        session_start();
        self::$started = true;

        // Regenerate ID periodically for security
        if (!isset($_SESSION['_last_regeneration'])) {
            $_SESSION['_last_regeneration'] = time();
        } elseif (time() - $_SESSION['_last_regeneration'] > 300) { // 5 minutes
            self::regenerate();
        }

        // Load flash messages
        if (isset($_SESSION['_flash'])) {
            self::$flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
        }
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate(bool $deleteOld = true): void
    {
        if (!self::$started) {
            self::start();
        }
        session_regenerate_id($deleteOld);
        $_SESSION['_last_regeneration'] = time();
    }

    /**
     * Get session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$started) {
            self::start();
        }
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public static function set(string $key, mixed $value): void
    {
        if (!self::$started) {
            self::start();
        }
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session has key
     */
    public static function has(string $key): bool
    {
        if (!self::$started) {
            self::start();
        }
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function forget(string $key): void
    {
        if (!self::$started) {
            self::start();
        }
        unset($_SESSION[$key]);
    }

    /**
     * Flush all session data
     */
    public static function flush(): void
    {
        if (!self::$started) {
            self::start();
        }
        $_SESSION = [];
    }

    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        if (!self::$started) {
            self::start();
        }
        $_SESSION = [];
        
        // Delete session cookie
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        
        session_destroy();
        self::$started = false;
    }

    /**
     * Get session ID
     */
    public static function id(): string
    {
        if (!self::$started) {
            self::start();
        }
        return session_id();
    }

    /**
     * Flash message (available for next request only)
     */
    public static function flash(string $type, string $message): void
    {
        if (!self::$started) {
            self::start();
        }
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Get flash messages
     */
    public static function getFlash(): array
    {
        return self::$flash;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    /**
     * Get authenticated user ID
     */
    public static function userId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * Get authenticated user role
     */
    public static function userRole(): ?string
    {
        return self::get('user_role');
    }

    /**
     * Get authenticated user data
     */
    public static function user(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'id' => self::get('user_id'),
            'name' => self::get('user_name'),
            'email' => self::get('user_email'),
            'role' => self::get('user_role'),
        ];
    }

    /**
     * Login user (set session data)
     */
    public static function login(array $user): void
    {
        if (!self::$started) {
            self::start();
        }
        
        // Regenerate ID to prevent fixation
        self::regenerate(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_logged_in_at'] = time();
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        self::destroy();
    }

    /**
     * Check session timeout
     */
    public static function checkTimeout(int $lifetime = 1800): bool
    {
        if (!self::has('user_logged_in_at')) {
            return false;
        }
        
        $lastActivity = self::get('user_logged_in_at');
        if (time() - $lastActivity > $lifetime) {
            self::logout();
            return false;
        }
        
        // Update last activity
        self::set('user_logged_in_at', time());
        return true;
    }
}