<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pageTitle = 'Verify Donations';

$pending = $pdo->query(
    "SELECT d.*, u.name AS donor_name, u.email AS donor_email
     FROM donations d
     JOIN users u ON u.user_id = d.donor_id
     WHERE d.status = 'pending' AND d.donation_type != 'Cash'
     ORDER BY d.created_at ASC"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Verify Donations</h1>
<p class="help-text">In-kind donations (food, clothes, books, medical supplies) wait here until we confirm physical receipt. Cash donations are verified automatically by the payment gateway.</p>

<?php if (empty($pending)): ?>
    <div class="empty-state">Nothing pending &mdash; all caught up.</div>
<?php else: ?>
<table>
    <tr><th>Date</th><th>Donor</th><th>Type</th><th>Details</th><th>Action</th></tr>
    <?php foreach ($pending as $d): ?>
    <tr>
        <td><?= formatDate($d['created_at']) ?></td>
        <td><?= clean($d['donor_name']) ?><br><span class="help-text"><?= clean($d['donor_email']) ?></span></td>
        <td><?= clean($d['donation_type']) ?></td>
        <td><?= $d['donation_type'] === 'Cash' ? formatCurrency($d['amount']) : clean($d['quantity']) ?><?= $d['notes'] ? '<br><span class="help-text">' . clean($d['notes']) . '</span>' : '' ?></td>
        <td>
            <form action="/hopehub/admin/verify_donation.php" method="post" style="display:inline;">
                <input type="hidden" name="donation_id" value="<?= (int)$d['donation_id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Verify</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
