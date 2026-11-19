<?php
$title = "Login";
require_once __DIR__ . '/includes/header.php';
if (isLoggedIn()) redirect('/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($pass, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'role' => $u['role']];
        setFlash('success', "Welcome back, {$u['name']}!");
        redirect('/dashboard.php');
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<div style="max-width:380px; margin:30px auto;">
    <div class="card card-body">
        <h2 style="font-size:1.3rem; text-align:center; margin-bottom:15px;">Account Login</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form action="/login.php" method="POST">
            <div class="form-group"><label>Email</label><input type="email" name="email" required placeholder="admin@realestate.test"></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required placeholder="password123"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
        </form>
        <p style="text-align:center; font-size:0.85rem; margin-top:12px; color:#64748b;">Don't have an account? <a href="/register.php">Register</a></p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
