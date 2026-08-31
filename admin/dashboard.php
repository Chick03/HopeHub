<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pageTitle = 'Admin Dashboard';

$stats = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM users WHERE role='donor') AS total_donors,
        (SELECT COUNT(*) FROM orphans) AS total_children,
        (SELECT COUNT(*) FROM needs WHERE status='open') AS open_needs,
        (SELECT COUNT(*) FROM donations WHERE status IN ('success','verified')) AS total_donations,
        (SELECT COALESCE(SUM(amount),0) FROM donations WHERE donation_type='Cash' AND status IN ('success','verified')) AS total_raised,
        (SELECT COUNT(*) FROM donations WHERE status='pending') AS pending_count"
)->fetch();

$topDonors = $pdo->query(
    "SELECT u.name, u.email, COUNT(d.donation_id) AS num_donations, COALESCE(SUM(d.amount),0) AS total_amount
     FROM donations d JOIN users u ON u.user_id = d.donor_id
     WHERE d.status IN ('success','verified')
     GROUP BY d.donor_id ORDER BY total_amount DESC LIMIT 5"
)->fetchAll();

$recentDonations = $pdo->query(
    "SELECT d.*, u.name AS donor_name
     FROM donations d
     JOIN users u ON u.user_id = d.donor_id
     ORDER BY d.created_at DESC LIMIT 8"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Admin Dashboard</h1>

<div class="stats-row">
    <div class="stat-card"><div class="stat-value"><?= (int)$stats['total_donors'] ?></div><div class="stat-label">Donors</div></div>
    <div class="stat-card"><div class="stat-value"><?= (int)$stats['total_children'] ?></div><div class="stat-label">Children in Care</div></div>
    <div class="stat-card"><div class="stat-value"><?= (int)$stats['open_needs'] ?></div><div class="stat-label">Open Needs</div></div>
    <div class="stat-card"><div class="stat-value"><?= (int)$stats['total_donations'] ?></div><div class="stat-label">Completed Donations</div></div>
    <div class="stat-card"><div class="stat-value"><?= formatCurrency($stats['total_raised']) ?></div><div class="stat-label">Cash Raised</div></div>
    <div class="stat-card"><div class="stat-value"><?= (int)$stats['pending_count'] ?></div><div class="stat-label">Pending Verification</div></div>
</div>

<div class="hero-actions" style="justify-content:flex-start; margin-bottom:32px;">
    <a href="/hopehub/admin/orphanage_profile.php" class="btn btn-secondary btn-sm">Orphanage Profile</a>
    <a href="/hopehub/admin/manage_content.php" class="btn btn-secondary btn-sm">Children & Needs</a>
    <a href="/hopehub/admin/verify_donations.php" class="btn btn-secondary btn-sm">Verify Donations</a>
    <a href="/hopehub/admin/reports.php" class="btn btn-secondary btn-sm">Reports</a>
    <a href="/hopehub/admin/manage_users.php" class="btn btn-secondary btn-sm">Manage Users</a>
</div>

<div class="two-col">
    <div>
        <h2>Recent Donations</h2>
        <table>
            <tr><th>Date</th><th>Donor</th><th>Type</th><th>Amount/Qty</th><th>Status</th></tr>
            <?php foreach ($recentDonations as $d): ?>
            <tr>
                <td><?= formatDate($d['created_at']) ?></td>
                <td><?= clean($d['donor_name']) ?></td>
                <td><?= clean($d['donation_type']) ?></td>
                <td><?= $d['donation_type'] === 'Cash' ? formatCurrency($d['amount']) : clean($d['quantity']) ?></td>
                <td><span class="badge badge-<?= $d['status'] === 'success' ? 'success' : ($d['status'] === 'failed' ? 'failed' : ($d['status'] === 'verified' ? 'verified' : 'pending')) ?>"><?= clean($d['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div>
        <h2>Top Donors</h2>
        <table>
            <tr><th>Donor</th><th>Total</th></tr>
            <?php foreach ($topDonors as $t): ?>
            <tr><td><?= clean($t['name']) ?></td><td><?= formatCurrency($t['total_amount']) ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
