<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pageTitle = 'Manage Users';

$donors = $pdo->query(
    "SELECT u.*, COUNT(d.donation_id) AS num_donations, COALESCE(SUM(CASE WHEN d.donation_type='Cash' THEN d.amount ELSE 0 END),0) AS total_given
     FROM users u
     LEFT JOIN donations d ON d.donor_id = u.user_id AND d.status IN ('success','verified')
     WHERE u.role = 'donor'
     GROUP BY u.user_id ORDER BY u.created_at DESC"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Manage Users</h1>

<table>
    <tr><th>Name</th><th>Email</th><th>Joined</th><th>Donations</th><th>Total Given</th><th>Status</th><th>Action</th></tr>
    <?php foreach ($donors as $d): ?>
    <tr>
        <td><?= clean($d['name']) ?></td>
        <td><?= clean($d['email']) ?></td>
        <td><?= formatDate($d['created_at']) ?></td>
        <td><?= (int)$d['num_donations'] ?></td>
        <td><?= formatCurrency($d['total_given']) ?></td>
        <td><span class="badge badge-<?= $d['status'] === 'active' ? 'success' : 'failed' ?>"><?= clean($d['status']) ?></span></td>
        <td>
            <form action="/hopehub/admin/user_toggle.php" method="post">
                <input type="hidden" name="user_id" value="<?= (int)$d['user_id'] ?>">
                <button type="submit" class="btn btn-sm <?= $d['status'] === 'active' ? 'btn-danger' : 'btn-secondary' ?>"
                    data-confirm="<?= $d['status'] === 'active' ? 'Suspend this user?' : 'Reactivate this user?' ?>">
                    <?= $d['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
