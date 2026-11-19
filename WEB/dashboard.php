<?php
$title = "Dashboard";
require_once __DIR__ . '/includes/header.php';
requireLogin();
$uid = $_SESSION['user']['id'];
$role = userRole();
// CRUD 1: Properties & Inquiry Reply (Agent/Admin only)
if ($role === 'agent' || $role === 'admin') {
    $uploadImg = function($default) {
        if (!empty($_FILES['image']['name']) && in_array($ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp'])) {
            $name = 'prop_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/assets/images/uploads/' . $name)) return '/assets/images/uploads/' . $name;
        }
        return $default;
    };
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
        $rawPrice = clean($_POST['price'] ?? '');
        if (!is_numeric($rawPrice) || (float)$rawPrice <= 0) {
            setFlash('danger', 'Please enter a valid positive numerical price.');
            redirect('/dashboard.php');
        }
        $pdo->prepare("INSERT INTO properties (user_id, title, price, price_type, city, image, description) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$uid, clean($_POST['title']), (float)$rawPrice, clean($_POST['price_type']), clean($_POST['city']), $uploadImg('/assets/images/placeholder.svg'), clean($_POST['description'])]);
        setFlash('success', 'Property published successfully!'); redirect('/dashboard.php');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_property'])) {
        $rawPrice = clean($_POST['price'] ?? '');
        if (!is_numeric($rawPrice) || (float)$rawPrice <= 0) {
            setFlash('danger', 'Please enter a valid positive numerical price.');
            redirect('/dashboard.php');
        }
        $pdo->prepare("UPDATE properties SET title = ?, price = ?, price_type = ?, city = ?, image = ?, description = ? WHERE id = ? AND (user_id = ? OR ? = 'admin')")
            ->execute([clean($_POST['title']), (float)$rawPrice, clean($_POST['price_type']), clean($_POST['city']), $uploadImg(clean($_POST['old_image'] ?? '/assets/images/placeholder.svg')), clean($_POST['description']), (int)$_POST['property_id'], $uid, $role]);
        setFlash('success', 'Property updated!'); redirect('/dashboard.php');
    }
    if (isset($_GET['del_prop'])) {
        $pdo->prepare("DELETE FROM properties WHERE id = ? AND (user_id = ? OR ? = 'admin')")->execute([(int)$_GET['del_prop'], $uid, $role]);
        setFlash('success', 'Property removed.'); redirect('/dashboard.php');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_inquiry'])) {
        $pdo->prepare("UPDATE inquiries i JOIN properties p ON i.property_id = p.id SET i.reply = ?, i.status = 'replied' WHERE i.id = ? AND (p.user_id = ? OR ? = 'admin')")->execute([clean($_POST['reply']), (int)$_POST['inquiry_id'], $uid, $role]);
        setFlash('success', 'Reply sent to client.'); redirect('/dashboard.php');
    }
}
// CRUD 2: Inquiry Delete
if (isset($_GET['del_inq'])) {
    $pdo->prepare("DELETE i FROM inquiries i JOIN properties p ON i.property_id = p.id WHERE i.id = ? AND (i.user_id = ? OR p.user_id = ? OR ? = 'admin')")->execute([(int)$_GET['del_inq'], $uid, $uid, $role]);
    setFlash('success', 'Inquiry deleted.'); redirect('/dashboard.php');
}
// Fetch Data
$myProps = ($role === 'admin') ? $pdo->query("SELECT * FROM properties ORDER BY id DESC")->fetchAll() : $pdo->prepare("SELECT * FROM properties WHERE user_id = ? ORDER BY id DESC");
if ($role !== 'admin') { $myProps->execute([$uid]); $myProps = $myProps->fetchAll(); }
$inquiries = ($role === 'customer') 
    ? $pdo->prepare("SELECT i.*, p.title AS prop_title FROM inquiries i JOIN properties p ON i.property_id = p.id WHERE i.user_id = ? ORDER BY i.id DESC")
    : $pdo->prepare("SELECT i.*, p.title AS prop_title, u.name AS sender_name, u.email AS sender_email FROM inquiries i JOIN properties p ON i.property_id = p.id JOIN users u ON i.user_id = u.id " . ($role === 'admin' ? "" : "WHERE p.user_id = ? ") . "ORDER BY i.id DESC");
if ($role === 'customer' || $role === 'agent') $inquiries->execute([$uid]); else $inquiries->execute();
$inquiries = $inquiries->fetchAll();
$editProp = null;
if (isset($_GET['edit']) && ($role === 'agent' || $role === 'admin')) {
    $epStmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND (user_id = ? OR ? = 'admin')");
    $epStmt->execute([(int)$_GET['edit'], $uid, $role]);
    $editProp = $epStmt->fetch();
}
?>
<h2 style="margin-bottom:20px;">Welcome, <?= escape($_SESSION['user']['name']) ?> <span class="badge"><?= ucfirst($role) ?></span></h2>
<?php if ($role === 'agent' || $role === 'admin'): ?>
    <div class="card card-body" style="margin-bottom:25px;">
        <h3><?= $editProp ? 'Edit Property' : 'Publish New Property (Entity 1 Create)' ?></h3>
        <form action="/dashboard.php" method="POST" enctype="multipart/form-data" style="margin-top:10px;">
            <?php if ($editProp): ?><input type="hidden" name="property_id" value="<?= $editProp['id'] ?>"><input type="hidden" name="old_image" value="<?= escape($editProp['image']) ?>"><?php endif; ?>
            <div class="grid-2">
                <div class="form-group"><label>Title</label><input type="text" name="title" required value="<?= escape($editProp['title'] ?? '') ?>"></div>
                <div class="form-group"><label>Price (BDT)</label><input type="number" name="price" required value="<?= $editProp['price'] ?? '' ?>"></div>
            </div>
            <div class="grid-2">
                <div class="form-group"><label>Type</label><select name="price_type"><option value="sale" <?= ($editProp['price_type'] ?? '') === 'sale' ? 'selected':'' ?>>For Sale</option><option value="rent" <?= ($editProp['price_type'] ?? '') === 'rent' ? 'selected':'' ?>>For Rent</option></select></div>
                <div class="form-group"><label>City</label><input type="text" name="city" required value="<?= escape($editProp['city'] ?? '') ?>"></div>
            </div>
            <div class="form-group"><label>Property Image</label><input type="file" name="image" accept="image/*"></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="2" required><?= escape($editProp['description'] ?? '') ?></textarea></div>
            <button type="submit" name="<?= $editProp ? 'edit_property':'add_property' ?>" class="btn btn-primary"><?= $editProp ? 'Save Changes':'Publish Listing' ?></button>
            <?php if ($editProp): ?><a href="/dashboard.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
        </form>
    </div>

    <div class="card card-body" style="margin-bottom:25px;">
        <h3>Managed Properties (<?= count($myProps) ?>)</h3>
        <table>
            <tr><th>Title</th><th>City</th><th>Price</th><th>Type</th><th style="text-align:right;">Action</th></tr>
            <?php foreach ($myProps as $mp): ?>
                <tr>
                    <td><strong><a href="/property-details.php?id=<?= $mp['id'] ?>"><?= escape($mp['title']) ?></a></strong></td>
                    <td><?= escape($mp['city']) ?></td>
                    <td>BDT <?= number_format($mp['price']) ?></td>
                    <td><?= ucfirst($mp['price_type']) ?></td>
                    <td style="text-align:right;">
                        <a href="/dashboard.php?edit=<?= $mp['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                        <a href="/dashboard.php?del_prop=<?= $mp['id'] ?>" onclick="return confirm('Delete property?');" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<div class="card card-body">
    <h3><?= $role === 'customer' ? 'My Sent Inquiries' : 'Client Inquiries (Entity 2 CRUD)' ?> (<?= count($inquiries) ?>)</h3>
    <?php if (empty($inquiries)): ?><p style="color:#64748b; margin-top:10px;">No inquiries found.</p><?php else: ?>
        <table>
            <tr><th>Property</th><th><?= $role === 'customer' ? 'Message' : 'Client & Message' ?></th><th>Status / Reply</th><th style="text-align:right;">Action</th></tr>
            <?php foreach ($inquiries as $iq): ?>
                <tr>
                    <td><a href="/property-details.php?id=<?= $iq['property_id'] ?>"><strong><?= escape($iq['prop_title']) ?></strong></a></td>
                    <td>
                        <?php if ($role !== 'customer'): ?><strong><?= escape($iq['sender_name']) ?></strong> (<?= escape($iq['sender_email']) ?>):<br><?php endif; ?>
                        "<?= escape($iq['message']) ?>"
                    </td>
                    <td>
                        <span class="badge badge-<?= $iq['status'] ?>"><?= ucfirst($iq['status']) ?></span>
                        <?php if ($iq['reply']): ?><br><small style="color:#16a34a;">Reply: <?= escape($iq['reply']) ?></small><?php endif; ?>
                        <?php if (($role === 'agent' || $role === 'admin') && !$iq['reply']): ?>
                            <form action="/dashboard.php" method="POST" style="margin-top:5px; display:flex; gap:5px;">
                                <input type="hidden" name="inquiry_id" value="<?= $iq['id'] ?>">
                                <input type="text" name="reply" placeholder="Type reply..." required style="padding:4px 6px; font-size:0.8rem;">
                                <button type="submit" name="reply_inquiry" class="btn btn-primary btn-sm">Send</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;"><a href="/dashboard.php?del_inq=<?= $iq['id'] ?>" onclick="return confirm('Delete inquiry?');" class="btn btn-danger btn-sm">Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

