<?php
require_once __DIR__ . '/config/database.php';
$_SESSION = [];
session_destroy();
session_start();
setFlash('success', 'Logged out successfully.');
redirect('/login.php');
