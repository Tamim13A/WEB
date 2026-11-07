# Phase 5.1: Security Hardening Measures Implementation
## CIS324 Real Estate Portal - Student: 241-16-034, Section: 19_A

---

## Hardening Measure 1: Session ID Regeneration + Secure Cookie Configuration

### Location: `app/Core/Session.php` & `app/Config/App.php`

### Before (Vulnerable):
```php
// No session regeneration on login
// Default PHP session settings (insecure)
session_start();
// Cookies accessible via JavaScript (no HttpOnly)
// Cookies sent over HTTP (no Secure flag)
// No SameSite policy
```

### After (Hardened):
```php
// app/Config/App.php - Secure session configuration
'session' => [
    'name' => 'realestate_session',
    'lifetime' => 1800, // 30 minutes
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Strict',
],

// app/Core/Session.php - Session hardening
public static function start(): void
{
    // Strict mode prevents session fixation
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
    
    // Periodic regeneration (every 5 minutes)
    if (!isset($_SESSION['_last_regeneration'])) {
        $_SESSION['_last_regeneration'] = time();
    } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
        self::regenerate();
    }
}

public static function login(array $user): void
{
    // Regenerate ID on login to prevent fixation
    self::regenerate(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_logged_in_at'] = time();
}

public static function regenerate(bool $deleteOld = true): void
{
    session_regenerate_id($deleteOld);
    $_SESSION['_last_regeneration'] = time();
}

public static function checkTimeout(int $lifetime = 1800): bool
{
    if (!self::has('user_logged_in_at')) {
        return false;
    }
    
    $lastActivity = self::get('user_logged_in_at');
    if (time() - $lastActivity > $lifetime) {
        self::logout(); // Destroy session on timeout
        return false;
    }
    
    // Update last activity on each request
    self::set('user_logged_in_at', time());
    return true;
}
```

### Security Benefits:
1. **Session Fixation Prevention**: `session_regenerate_id(true)` on login destroys old session
2. **Session Hijacking Mitigation**: HttpOnly prevents XSS theft, Secure ensures HTTPS-only transmission
3. **CSRF Protection**: SameSite=Strict blocks cross-origin requests
4. **Automatic Timeout**: 30-minute inactivity destroys session
5. **Periodic Regeneration**: New session ID every 5 minutes limits exposure window

---

## Hardening Measure 2: Password Hashing with Argon2id + Timing Attack Prevention

### Location: `app/Config/Security.php` & `app/Model/User.php`

### Before (Vulnerable):
```php
// Weak hashing (MD5/SHA1) or no hashing
$hash = md5($password); // FAST - vulnerable to brute force
// Direct string comparison - timing attacks
if ($input === $storedHash) { ... }
```

### After (Hardened):
```php
// app/Config/Security.php
public static function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,    // 64 MB memory
        'time_cost' => 4,          // 4 iterations
        'threads' => 3,            // 3 parallel threads
    ]);
}

public static function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

public static function needsRehash(string $hash): bool
{
    return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3,
    ]);
}

public static function hashEquals(string $known, string $user): bool
{
    return hash_equals($known, $user); // Constant-time comparison
}

// app/Model/User.php
public function createUser(array $data): int
{
    if (isset($data['password'])) {
        $data['password_hash'] = Security::hashPassword($data['password']);
        unset($data['password']);
    }
    return $this->create($data);
}

public function verifyPassword(int $id, string $password): bool
{
    $user = $this->find($id);
    if (!$user) return false;
    return Security::verifyPassword($password, $user['password_hash']);
}

public function changePassword(int $id, string $newPassword): int
{
    return $this->update($id, ['password_hash' => Security::hashPassword($newPassword)]);
}
```

### Security Benefits:
1. **Argon2id**: Memory-hard function resistant to GPU/ASIC cracking
2. **High Memory Cost (64MB)**: Makes parallel attacks expensive
3. **Constant-Time Verification**: `hash_equals()` prevents timing attacks
4. **Automatic Rehash Detection**: `password_needs_rehash()` upgrades old hashes
5. **Salt Included**: Each hash contains unique salt (no rainbow tables)

---

## Additional Hardening Implemented (Bonus)

### 3. CSRF Protection (All State-Changing Requests)
```php
// app/Config/Security.php
public static function generateCsrfToken(): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['_csrf_tokens'][$token] = time() + 3600;
    return $token;
}

public static function validateCsrfToken(string $token): bool
{
    if (empty($_SESSION['_csrf_tokens'][$token])) return false;
    if ($_SESSION['_csrf_tokens'][$token] < time()) {
        unset($_SESSION['_csrf_tokens'][$token]);
        return false;
    }
    unset($_SESSION['_csrf_tokens'][$token]); // One-time use
    return true;
}

// app/Middleware/CsrfMiddleware.php - Applied to all POST/PUT/PATCH/DELETE
```

### 4. SQL Injection Prevention (Prepared Statements Everywhere)
```php
// app/Core/Database.php - All queries use prepared statements
public static function fetchOne(string $sql, array $params = []): ?array
{
    $stmt = self::getInstance()->prepare($sql); // PREPARED
    $stmt->execute($params);                     // PARAMETERIZED
    return $stmt->fetch() ?: null;
}

// Example usage in Models:
$sql = "SELECT * FROM users WHERE email = ?"; // Placeholder
return Database::fetchOne($sql, [$email]);    // Bound parameter
```

### 5. XSS Prevention (Context-Aware Output Escaping)
```php
// app/Config/Security.php
public static function escape(string $value, int $flags = ENT_QUOTES, string $encoding = 'UTF-8'): string
{
    return htmlspecialchars($value, $flags, $encoding);
}

// In Views (property_card.php):
<h3><?= Security::escape($property['title']) ?></h3>
// In JavaScript context:
<div data-message="<?= Security::escape($message, ENT_QUOTES) ?>"></div>
```

### 6. Security Headers (CSP, HSTS, etc.)
```php
// app/Core/Response.php
public static function setSecurityHeaders(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://cdn.jsdelivr.net; " .
           "connect-src 'self';";
    header("Content-Security-Policy: {$csp}");
}
```

---

## Summary of Hardening Measures for Report

| Measure | Files | Threat Mitigated | Q7 Requirement |
|---------|-------|------------------|----------------|
| **1. Session Hardening** | `Session.php`, `App.php` | Session Hijacking, Fixation, CSRF | ✅ 2+ measures |
| **2. Argon2id Password Hashing** | `Security.php`, `User.php` | Weak Password Storage, Brute Force, Timing Attacks | ✅ 2+ measures |
| **3. CSRF Tokens** | `Security.php`, `CsrfMiddleware.php` | Cross-Site Request Forgery | Bonus |
| **4. Prepared Statements** | `Database.php`, All Models | SQL Injection | Required for Q4/Q5 |
| **5. Output Escaping** | `Security.php`, All Views | Cross-Site Scripting (XSS) | Required for Q5 |
| **6. Security Headers/CSP** | `Response.php` | XSS, Clickjacking, MIME Sniffing | Bonus |

**Total: 6 hardening measures implemented (requirement: ≥2)** ✅