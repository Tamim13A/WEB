<?php
/**
 * Security Helpers - Essential only
 * CIS324 Real Estate Portal
 */

class Security
{
    public static function escape(string $v, int $flags = ENT_QUOTES): string
    {
        return htmlspecialchars($v, $flags, 'UTF-8');
    }

    public static function hashPassword(string $p): string
    {
        return password_hash($p, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
    }

    public static function verifyPassword(string $p, string $h): bool
    {
        return password_verify($p, $h);
    }

    public static function generateCsrfToken(): string
    {
        $t = bin2hex(random_bytes(32));
        $_SESSION['_csrf'][$t] = time() + 3600;
        return $t;
    }

    public static function validateCsrfToken(string $t): bool
    {
        if (empty($_SESSION['_csrf'][$t]) || $_SESSION['_csrf'][$t] < time()) return false;
        unset($_SESSION['_csrf'][$t]);
        return true;
    }

    public static function validateEmail(string $e): bool
    {
        return filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePhone(string $p): bool
    {
        return preg_match('/^(\+8801|01)[3-9]\d{8}$/', $p) === 1;
    }

    public static function validatePrice(string $p): bool
    {
        return preg_match('/^\d+(\.\d{1,2})?$/', $p) === 1 && (float)$p > 0;
    }

    public static function validateRequired(string $v): bool
    {
        return trim($v) !== '';
    }

    public static function validateImageUpload(array $f, array $cfg): array
    {
        $err = [];
        if ($f['error'] !== UPLOAD_ERR_OK) return ['Upload error: ' . $f['error']];
        if ($f['size'] > $cfg['max_size']) $err[] = 'Max ' . ($cfg['max_size'] / 1024 / 1024) . 'MB';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $cfg['allowed_mimes'], true)) $err[] = 'Invalid type';
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $cfg['allowed_extensions'], true)) $err[] = 'Invalid ext';
        if (getimagesize($f['tmp_name']) === false) $err[] = 'Not an image';
        return $err;
    }

    public static function secureFilename(string $name): string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg','jpeg','png','webp']) ? bin2hex(random_bytes(16)) . '_' . time() . ".{$ext}" : bin2hex(random_bytes(16)) . '_' . time() . '.jpg';
    }
}