<?php
$title = "Explore Properties";
require_once __DIR__ . '/includes/header.php';

require_once __DIR__ . '/models/Property.php';

$search = clean($_GET['search'] ?? '');
$type = clean($_GET['type'] ?? '');
// User state management via Cookies (remember preferred listing type)
if ($type) {
    setcookie('pref_type', $type, [
        'expires'  => time() + (86400 * 30),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} elseif (empty($type) && !empty($_COOKIE['pref_type'])) {
    $type = clean($_COOKIE['pref_type']);
}

$properties = Property::all($pdo, $search, $type);
?>
<section style="background:#1e293b; color:#fff; padding:20px; border-radius:8px; margin-bottom:25px;">
    <h2 style="font-size:1.3rem; margin-bottom:10px;">Find Your Next Property</h2>
    <div style="display:grid; grid-template-columns:1fr 140px; gap:10px;">
        <input type="text" id="live-search" placeholder="Search by title or city (Fetch API)..." value="<?= escape($search) ?>">
        <select id="filter-type">
            <option value="">All Types</option>
            <option value="sale" <?= $type === 'sale' ? 'selected':'' ?>>Sale</option>
            <option value="rent" <?= $type === 'rent' ? 'selected':'' ?>>Rent</option>
        </select>
    </div>
</section>

<h3 style="margin-bottom:15px;">Available Properties</h3>
<div id="property-grid" class="grid-3">
    <?php if (empty($properties)): ?>
        <p style="color:#64748b; text-align:center; grid-column:1/-1;">No properties found.</p>
    <?php else: ?>
        <?php foreach ($properties as $p): ?>
            <div class="card">
                <img src="<?= escape($p['image'] ?: '/assets/images/placeholder.svg') ?>" alt="<?= escape($p['title']) ?>" class="prop-img">
                <div class="card-body">
                    <span class="badge" style="margin-bottom:8px;">For <?= strtoupper($p['price_type']) ?></span>
                    <h3 style="font-size:1.1rem; margin-bottom:5px;"><a href="/property-details.php?id=<?= $p['id'] ?>" style="color:#0f172a; text-decoration:none;"><?= escape($p['title']) ?></a></h3>
                    <p style="color:#64748b; font-size:0.85rem;">📍 <?= escape($p['city']) ?> &bull; Agent: <?= escape($p['agent_name']) ?></p>
                    <div style="font-size:1.15rem; font-weight:700; color:#2563eb; margin:8px 0;">BDT <?= number_format($p['price']) ?><?= $p['price_type'] === 'rent' ? '/mo':'' ?></div>
                    <a href="/property-details.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" style="width:100%;">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
