<?php
$title = "Register";
require_once __DIR__ . '/includes/header.php';
if (isLoggedIn()) redirect('/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $role = (clean($_POST['role'] ?? '') === 'agent') ? 'agent' : 'customer';
    $pass = $_POST['password'] ?? '';
    if (!preg_match('/^[a-zA-Z\s.\'-]{2,100}$/', $name)) {
        $error = "Name must be 2-100 characters and contain only letters, spaces, hyphens, periods, or apostrophes.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)")->execute([$name, $email, $hash, $role]);
            setFlash('success', 'Registration successful! You may now login.');
            redirect('/login.php');
        }
    }
}
?>
<div style="max-width:380px; margin:30px auto;">
    <div class="card card-body">
        <h2 style="font-size:1.3rem; text-align:center; margin-bottom:15px;">Create Account</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form action="/register.php" method="POST">
            <div class="form-group"><label>Role</label><select name="role"><option value="customer">Customer / Buyer</option><option value="agent">Property Agent / Owner</option></select></div>
            <div class="form-group"><label>Full Name</label><input type="text" name="name" required placeholder="e.g. Tamim"></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required placeholder="tamim@example.com"></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required placeholder="Min 6 characters"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
        </form>
        <p style="text-align:center; font-size:0.85rem; margin-top:12px; color:#64748b;">Have an account? <a href="/login.php">Login</a></p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
