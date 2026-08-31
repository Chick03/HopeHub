<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_donor();

$pageTitle = 'My Donations';
$donorId = current_user()['user_id'];

$stmt = $pdo->prepare(
    "SELECT d.*, r.receipt_id
     FROM donations d
     LEFT JOIN receipts r ON r.donation_id = d.donation_id
     WHERE d.donor_id = ? ORDER BY d.created_at DESC"
);
$stmt->execute([$donorId]);
$donations = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>My Donations</h1>

<?php if (empty($donations)): ?>
    <div class="empty-state">No donations yet. <a href="/hopehub/donor/donate.php">Make your first one</a>.</div>
<?php else: ?>
<table>
    <tr><th>Date</th><th>Type</th><th>Amount / Qty</th><th>Status</th><th>Receipt</th></tr>
    <?php foreach ($donations as $d): ?>
    <tr>
        <td><?= formatDate($d['created_at']) ?></td>
        <td><?= clean($d['donation_type']) ?></td>
        <td><?= $d['donation_type'] === 'Cash' ? formatCurrency($d['amount']) : clean($d['quantity']) ?></td>
        <td>
            <span class="badge badge-<?= $d['status'] === 'success' ? 'success' : ($d['status'] === 'failed' ? 'failed' : ($d['status'] === 'verified' ? 'verified' : 'pending')) ?>">
                <?= clean($d['status']) ?>
            </span>
        </td>
        <td>
            <?php if ($d['receipt_id']): ?>
                <a href="/hopehub/receipt/download.php?donation_id=<?= (int)$d['donation_id'] ?>" class="btn btn-outline btn-sm">Download</a>
            <?php elseif ($d['status'] === 'failed'): ?>
                <a href="/hopehub/payment/checkout.php?donation_id=<?= (int)$d['donation_id'] ?>" class="btn btn-secondary btn-sm">Retry</a>
            <?php else: ?>
                &mdash;
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
