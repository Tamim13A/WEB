<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
try {
    $pdo = new PDO("mysql:host=localhost;dbname=realestate_portal;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) { die("Database Error: " . $e->getMessage()); }

function clean($d) { return trim(strip_tags((string)$d)); }
function escape($d) { return htmlspecialchars((string)$d, ENT_QUOTES, 'UTF-8'); }
function redirect($u) { header("Location: $u"); exit; }
function isLoggedIn() { return isset($_SESSION['user']['id']); }
function userRole() { return $_SESSION['user']['role'] ?? ''; }
function requireLogin() { if (!isLoggedIn()) { setFlash('danger', 'Please login first.'); redirect('/login.php'); } }
function setFlash($t, $m) { $_SESSION['flash'] = ['type' => $t, 'msg' => $m]; }
function showFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']);
        echo "<div class='alert alert-{$f['type']}'>{$f['msg']}</div>";
    }
}