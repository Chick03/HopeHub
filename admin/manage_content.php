<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pageTitle = 'Children & Needs';

$orphans = $pdo->query("SELECT * FROM orphans ORDER BY age")->fetchAll();
$needs = $pdo->query("SELECT * FROM needs ORDER BY (status='open') DESC, created_at DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Children & Needs</h1>
<p class="help-text">Manage the children in care and the current wishlist &mdash; both shown on the public homepage.</p>

<div class="two-col">
    <div>
        <h2>Children (<?= count($orphans) ?>)</h2>
        <table>
            <tr><th>Name</th><th>Age</th><th>Gender</th><th></th></tr>
            <?php foreach ($orphans as $ch): ?>
            <tr>
                <td><?= clean($ch['name']) ?></td>
                <td><?= (int)$ch['age'] ?></td>
                <td><?= clean($ch['gender']) ?></td>
                <td>
                    <form action="/hopehub/admin/orphan_delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="orphan_id" value="<?= (int)$ch['orphan_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Remove this child's record?">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="margin-top:24px;">Add a Child</h3>
        <form action="/hopehub/admin/orphan_save.php" method="post" class="validate">
            <div class="form-row">
                <div class="form-group"><label for="orphan_name">Name</label><input type="text" name="name" id="orphan_name" required></div>
                <div class="form-group"><label for="orphan_age">Age</label><input type="number" name="age" id="orphan_age" min="0" max="18" required></div>
            </div>
            <div class="form-group">
                <label for="orphan_gender">Gender</label>
                <select name="gender" id="orphan_gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-block">Add Child</button>
        </form>
    </div>

    <div>
        <h2>Needs / Wishlist</h2>
        <div class="need-tags">
        <?php foreach ($needs as $n): ?>
            <span class="need-tag <?= $n['status'] === 'fulfilled' ? 'fulfilled' : '' ?>">
                <?= (int)$n['quantity'] ?> &times; <?= clean($n['item_name']) ?>
            </span>
        <?php endforeach; ?>
        </div>
        <table>
            <tr><th>Item</th><th>Category</th><th>Qty</th><th>Status</th><th></th></tr>
            <?php foreach ($needs as $n): ?>
            <tr>
                <td><?= clean($n['item_name']) ?></td>
                <td><?= clean($n['category']) ?></td>
                <td><?= (int)$n['quantity'] ?></td>
                <td><?= clean($n['status']) ?></td>
                <td style="white-space:nowrap;">
                    <?php if ($n['status'] === 'open'): ?>
                    <form action="/hopehub/admin/need_save.php" method="post" style="display:inline;">
                        <input type="hidden" name="need_id" value="<?= (int)$n['need_id'] ?>">
                        <input type="hidden" name="mark_fulfilled" value="1">
                        <button type="submit" class="btn btn-outline btn-sm">Mark Fulfilled</button>
                    </form>
                    <?php endif; ?>
                    <form action="/hopehub/admin/need_delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="need_id" value="<?= (int)$n['need_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this need?">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="margin-top:24px;">Post a Need</h3>
        <form action="/hopehub/admin/need_save.php" method="post" class="validate">
            <div class="form-group"><label for="item_name">Item</label><input type="text" name="item_name" id="item_name" placeholder="e.g. Blankets" required></div>
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" required>
                        <option>Cash</option><option>Food</option><option>Clothes</option><option>Books</option><option>Medical</option><option>Other</option>
                    </select>
                </div>
                <div class="form-group"><label for="need_qty">Quantity</label><input type="number" name="quantity" id="need_qty" min="1" value="1" required></div>
            </div>
            <button type="submit" class="btn btn-secondary btn-block">Post Need</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
