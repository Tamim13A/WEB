# Phase 5.3: Security Test Cases
## CIS324 Real Estate Portal - Student: 241-16-034, Section: 19_A

---

## Test Execution Instructions

```bash
# 1. Setup database
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql

# 2. Configure .env
cp .env.example .env
# Edit .env with database credentials

# 3. Start web server (Apache/XAMPP)
# Document root: public/

# 4. Run tests below and record results
```

---

## Test Suite 1: SQL Injection Tests

| Test ID | Requirement | Test Input | Expected Result | Actual Result | Pass/Fail |
|---------|-------------|------------|-----------------|---------------|-----------|
| **SQL-01** | Q5: SQLi Prevention | Login email: `' OR '1'='1' --` | "Invalid email or password" | | ⬜ |
| **SQL-02** | Q5: SQLi Prevention | Login email: `admin' --` | "Invalid email or password" | | ⬜ |
| **SQL-03** | Q5: SQLi Prevention | Login email: `' UNION SELECT 1,2,3,4,5,6,7,8,9--` | "Invalid email or password" | | ⬜ |
| **SQL-04** | Q5: SQLi Prevention | Search: `'; DROP TABLE users; --` | Safe search, no error | | ⬜ |
| **SQL-05** | Q5: SQLi Prevention | Search: `' UNION SELECT password FROM users--` | No password leak | | ⬜ |
| **SQL-06** | Q5: SQLi Prevention | Property ID: `/properties/1' OR '1'='1` | 404 Not Found | | ⬜ |
| **SQL-07** | Q5: SQLi Prevention | Property ID: `/properties/1 UNION SELECT * FROM users` | 404 Not Found | | ⬜ |
| **SQL-08** | Q5: SQLi Prevention | API: `/api/properties?search=' UNION SELECT * FROM users--` | Empty JSON array | | ⬜ |
| **SQL-09** | Q5: SQLi Prevention | Registration email: `test'; DROP TABLE users; --@test.com` | Validation error | | ⬜ |
| **SQL-10** | Q5: SQLi Prevention | Property title: `Test'; UPDATE users SET role='admin' WHERE id=1; --` | Saved as literal text | | ⬜ |

### SQLi Test Commands
```bash
# SQL-01: Auth bypass attempt
curl -X POST http://localhost/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=' OR '1'='1' --&password=anything&_csrf_token=TOKEN"

# SQL-04: Search injection
curl "http://localhost/properties?search=%27%3B+DROP+TABLE+users%3B+--"

# SQL-08: API injection
curl "http://localhost/api/properties?search=%27+UNION+SELECT+*+FROM+users--"

# SQL-06: Property ID injection
curl "http://localhost/properties/1%27+OR+%271%27%3D%271"
```

---

## Test Suite 2: Cross-Site Scripting (XSS) Tests

| Test ID | Requirement | Test Input | Expected Result | Actual Result | Pass/Fail |
|---------|-------------|------------|-----------------|---------------|-----------|
| **XSS-01** | Q5: XSS Prevention | Property title: `<script>alert('XSS')</script>` | Displayed as text | | ⬜ |
| **XSS-02** | Q5: XSS Prevention | Property title: `<img src=x onerror=alert(1)>` | Displayed as text | | ⬜ |
| **XSS-03** | Q5: XSS Prevention | Description: `<svg onload=alert('XSS')>` | Displayed as text | | ⬜ |
| **XSS-04** | Q5: XSS Prevention | Inquiry message: `<script>fetch('/api/favourites/toggle',{method:'POST',body:JSON.stringify({property_id:1})})</script>` | Displayed as text | | ⬜ |
| **XSS-05** | Q5: XSS Prevention | Agent response: `javascript:alert(document.cookie)` | Displayed as text | | ⬜ |
| **XSS-06** | Q5: XSS Prevention | Search: `<script>alert('XSS')</script>` | Displayed in results | | ⬜ |
| **XSS-07** | Q5: XSS Prevention | Register name: `<img src=x onerror=steal()>` | Displayed in dashboard | | ⬜ |
| **XSS-08** | Q5: XSS Prevention | Property address: `"><script>alert(1)</script>` | Displayed as text | | ⬜ |
| **XSS-09** | Q5: XSS Prevention | CSP blocks inline script execution | Console shows CSP violation | | ⬜ |
| **XSS-10** | Q5: XSS Prevention | JSON API response: proper Content-Type | `application/json` header | | ⬜ |

### XSS Test Commands
```bash
# XSS-01: Create property with XSS title (as agent)
curl -X POST http://localhost/agent/properties \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "title=%3Cscript%3Ealert('XSS')%3C%2Fscript%3E&description=Test&price=1000000&price_type=sale&address=Test&city=Dhaka&category_id=1&bedrooms=2&bathrooms=1&_csrf_token=TOKEN"

# XSS-04: Submit inquiry with XSS
curl -X POST http://localhost/api/inquiries \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: TOKEN" \
  -d '{"property_id":1,"message":"<script>fetch(\"/api/favourites/toggle\",{method:\"POST\",body:JSON.stringify({property_id:1})})</script>"}'

# XSS-06: Search XSS
curl "http://localhost/properties?search=%3Cscript%3Ealert('XSS')%3C%2Fscript%3E"

# XSS-09: Check CSP headers
curl -I http://localhost/ | grep -i content-security-policy
```

---

## Test Suite 3: Weak Password Storage Tests

| Test ID | Requirement | Test Input | Expected Result | Actual Result | Pass/Fail |
|---------|-------------|------------|-----------------|---------------|-----------|
| **PWD-01** | Q5: Password Hashing | Register with "password123" | Hash starts with `$argon2id$v=19$m=65536,t=4,p=3$` | | ⬜ |
| **PWD-02** | Q5: Password Hashing | Login with correct password | Success | | ⬜ |
| **PWD-03** | Q5: Password Hashing | Login with wrong password | "Invalid email or password" | | ⬜ |
| **PWD-04** | Q5: Password Hashing | Database shows no plain text passwords | All hashes are Argon2id | | ⬜ |
| **PWD-04** | Q5: Password Hashing | Hash contains unique salt per user | Different hashes for same password | | ⬜ |
| **PWD-05** | Q5: Timing Attack | Time valid vs invalid user login | Similar response times (±10ms) | | ⬜ |
| **PWD-06** | Q5: Password Hashing | Password upgrade detection | `needsRehash()` works | | ⬜ |
| **PWD-07** | Q5: Password Hashing | Minimum 8 chars enforced | Registration fails for "1234567" | | ⬜ |
| **PWD-08** | Q5: Password Hashing | Hash verification uses `password_verify()` | Constant-time comparison | | ⬜ |
| **PWD-09** | Q5: Password Hashing | Change password updates hash | New Argon2id hash stored | | ⬜ |
| **PWD-10** | Q5: Password Hashing | Brute force resistance | 64MB memory cost configured | | ⬜ |

### Password Test Commands
```bash
# PWD-01: Register and check hash
curl -X POST http://localhost/register \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "name=Test User&email=testpw@example.com&password=password123&password_confirmation=password123&_csrf_token=TOKEN"

# Check database
mysql -u root -p -e "SELECT email, password_hash FROM users WHERE email='testpw@example.com';"

# PWD-02: Login with correct password
curl -X POST http://localhost/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=testpw@example.com&password=password123&_csrf_token=TOKEN"

# PWD-03: Login with wrong password
curl -X POST http://localhost/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=testpw@example.com&password=wrongpassword&_csrf_token=TOKEN"

# PWD-05: Timing test (run multiple times)
for i in {1..10}; do 
  time curl -s -o /dev/null -w "%{time_total}\n" -X POST http://localhost/login \
    -d "email=exist@example.com&password=wrong&_csrf_token=TOKEN"; 
done

for i in {1..10}; do 
  time curl -s -o /dev/null -w "%{time_total}\n" -X POST http://localhost/login \
    -d "email=nonexist@example.com&password=wrong&_csrf_token=TOKEN"; 
done
```

---

## Test Suite 4: Session Security Tests

| Test ID | Requirement | Test Input | Expected Result | Actual Result | Pass/Fail |
|---------|-------------|------------|-----------------|---------------|-----------|
| **SES-01** | Q7: Session Fixation | Login → Check session ID changed | New session ID after login | | ⬜ |
| **SES-02** | Q7: HttpOnly Cookie | Check `document.cookie` in JS | Session cookie not accessible | | ⬜ |
| **SES-03** | Q7: Secure Cookie | HTTPS deployment | Cookie has `Secure` flag | | ⬜ |
| **SES-04** | Q7: SameSite Cookie | Cross-site request | `SameSite=Strict` blocks CSRF | | ⬜ |
| **SES-05** | Q7: Session Timeout | Wait 30+ min inactive | Auto logout | | ⬜ |
| **SES-06** | Q7: Session Regeneration | Login → Wait 5 min → Request | New session ID | | ⬜ |
| **SES-07** | Q7: Session Destruction | Logout → Check session gone | Session destroyed | | ⬜ |
| **SES-08** | Q7: Concurrent Sessions | Login from 2 browsers | Both work (or 2nd invalidates 1st) | | ⬜ |

---

## Test Results Template (Copy to Report)

### SQL Injection Test Results
| Test ID | Status | Evidence (Screenshot/Log) | Notes |
|---------|--------|---------------------------|-------|
| SQL-01 | ⬜ Pass / ❌ Fail | | |
| SQL-02 | ⬜ Pass / ❌ Fail | | |
| ... | | | |

### XSS Test Results
| Test ID | Status | Evidence (Screenshot/Log) | Notes |
|---------|--------|---------------------------|-------|
| XSS-01 | ⬜ Pass / ❌ Fail | | |
| XSS-02 | ⬜ Pass / ❌ Fail | | |
| ... | | | |

### Password Security Test Results
| Test ID | Status | Evidence (Screenshot/Log) | Notes |
|---------|--------|---------------------------|-------|
| PWD-01 | ⬜ Pass / ❌ Fail | | |
| PWD-02 | ⬜ Pass / ❌ Fail | | |
| ... | | | |

---

## Automation Script (Optional)

```php
<?php
// tests/security_test.php - Run from CLI after setup
require_once __DIR__ . '/../vendor/autoload.php';

$baseUrl = 'http://localhost';
$csrfToken = ''; // Get from page

$tests = [
    'SQL-01' => ['POST', '/login', ['email' => "' OR '1'='1' --", 'password' => 'x']],
    'SQL-04' => ['GET', '/properties?search=' . urlencode("'; DROP TABLE users; --")],
    'XSS-01' => ['POST', '/agent/properties', ['title' => '<script>alert(1)</script>', ...]],
    // ... more tests
];

foreach ($tests as $id => $test) {
    // Run test, check response, log result
    $result = runTest($id, $test);
    echo "$id: " . ($result ? 'PASS' : 'FAIL') . "\n";
}
```

---

## Evidence Collection Checklist

For each test, collect:
- [ ] **Screenshot** of browser/console showing result
- [ ] **HTTP Response** (headers + body) 
- [ ] **Database State** (for SQLi tests)
- [ ] **Console Log** (for XSS/CSP tests)
- [ ] **Timing Data** (for password timing tests)

**Record all in report with Test ID reference.**