<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_donor();

$pageTitle = 'My Dashboard';
$donorId = current_user()['user_id'];

$stats = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_donations,
        COALESCE(SUM(CASE WHEN donation_type='Cash' AND status IN ('success','verified') THEN amount ELSE 0 END),0) AS total_given,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending_count
     FROM donations WHERE donor_id = ?"
);
$stats->execute([$donorId]);
$stats = $stats->fetch();

$needsStmt = $pdo->query(
    "SELECT * FROM needs WHERE status = 'open' ORDER BY created_at DESC LIMIT 5"
);
$openNeeds = $needsStmt->fetchAll();

$recentStmt = $pdo->prepare(
    "SELECT * FROM donations WHERE donor_id = ? ORDER BY created_at DESC LIMIT 5"
);
$recentStmt->execute([$donorId]);
$recent = $recentStmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Welcome back, <?= clean(current_user()['name']) ?></h1>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['total_donations'] ?></div>
        <div class="stat-label">Total Donations</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= formatCurrency($stats['total_given']) ?></div>
        <div class="stat-label">Cash Contributed</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['pending_count'] ?></div>
        <div class="stat-label">Pending Verification</div>
    </div>
</div>

<div class="two-col">
    <div>
        <div class="section-title"><h2>Recent Donations</h2>
            <a href="/hopehub/donor/donation_history.php">View all &rarr;</a>
        </div>
        <?php if (empty($recent)): ?>
            <div class="empty-state">You haven't made any donations yet. <a href="/hopehub/donor/donate.php">Make your first one</a>.</div>
        <?php else: ?>
        <table>
            <tr><th>Type</th><th>Amount / Qty</th><th>Status</th></tr>
            <?php foreach ($recent as $d): ?>
            <tr>
                <td><?= clean($d['donation_type']) ?></td>
                <td><?= $d['donation_type'] === 'Cash' ? formatCurrency($d['amount']) : clean($d['quantity']) ?></td>
                <td><span class="badge badge-<?= $d['status'] === 'success' ? 'success' : ($d['status'] === 'failed' ? 'failed' : ($d['status'] === 'verified' ? 'verified' : 'pending')) ?>"><?= clean($d['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <div>
        <h2>Open Needs</h2>
        <?php if (empty($openNeeds)): ?>
            <p class="help-text">No open needs right now.</p>
        <?php endif; ?>
        <?php foreach ($openNeeds as $n): ?>
        <div class="card" style="margin-bottom:12px;">
            <div class="card-body">
                <div class="need-tags"><span class="need-tag"><?= (int)$n['quantity'] ?> &times; <?= clean($n['item_name']) ?></span></div>
                <a href="/hopehub/donor/donate.php?need_id=<?= (int)$n['need_id'] ?>" class="btn btn-outline btn-sm">Donate This</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
