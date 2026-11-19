<?php require_once __DIR__ . '/../config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? escape($title) . ' - Real Estate' : 'Real Estate Portal' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-wrap">
        <a href="/index.php" class="brand">🏠 RealEstate</a>
        <div class="nav-links">
            <a href="/index.php">Properties</a>
            <?php if (isLoggedIn()): ?>
                <a href="/dashboard.php">Dashboard (<?= ucfirst(userRole()) ?>)</a>
                <a href="/logout.php" style="color:#ef4444;">Logout</a>
            <?php else: ?>
                <a href="/login.php">Login</a>
                <a href="/register.php" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container">
<?php showFlash(); ?>
