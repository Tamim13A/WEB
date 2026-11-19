<?php
$id = (int)($_GET['id'] ?? 0);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Property.php';

$p = Property::find($pdo, $id);
if (!$p) redirect('/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_inquiry'])) {
    requireLogin();
    $msg = clean($_POST['message'] ?? '');
    if ($msg) {
        $pdo->prepare("INSERT INTO inquiries (property_id, user_id, message) VALUES (?, ?, ?)")->execute([$id, $_SESSION['user']['id'], $msg]);
        setFlash('success', 'Your inquiry has been sent to the agent!');
        redirect("/property-details.php?id=$id");
    }
}
$title = $p['title'];
?>
<div class="grid-2">
    <div class="card">
        <img src="<?= escape($p['image'] ?: '/assets/images/placeholder.svg') ?>" alt="<?= escape($p['title']) ?>" style="width:100%; height:280px; object-fit:cover; display:block;">
        <div class="card-body">
            <span class="badge" style="margin-bottom:8px;">For <?= strtoupper($p['price_type']) ?></span>
            <h1 style="font-size:1.5rem; margin-bottom:10px;"><?= escape($p['title']) ?></h1>
            <p style="color:#64748b; margin-bottom:15px;">📍 Location: <?= escape($p['city']) ?></p>
            <div style="font-size:1.4rem; font-weight:700; color:#2563eb; margin-bottom:15px;">BDT <?= number_format($p['price']) ?><?= $p['price_type'] === 'rent' ? '/mo':'' ?></div>
            <h3 style="margin-bottom:8px;">Property Overview</h3>
            <p style="line-height:1.6; color:#334155;"><?= nl2br(escape($p['description'])) ?></p>
        </div>
    </div>
    <div class="card card-body" style="height:fit-content;">
        <h3 style="margin-bottom:10px;">Contact Agent</h3>
        <p style="font-size:0.9rem; color:#64748b; margin-bottom:15px;">Listed by: <strong><?= escape($p['agent_name']) ?></strong> (<?= escape($p['agent_email']) ?>)</p>
        <?php if (!isLoggedIn()): ?>
            <a href="/login.php" class="btn btn-primary" style="display:block;">Login to Send Inquiry</a>
        <?php else: ?>
            <form action="/property-details.php?id=<?= $id ?>" method="POST">
                <div class="form-group"><label>Inquiry Message</label><textarea name="message" rows="4" required placeholder="Hello, I am interested in this listing..."></textarea></div>
                <button type="submit" name="send_inquiry" class="btn btn-primary" style="width:100%;">Submit Inquiry</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
