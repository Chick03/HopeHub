<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_donor();

$pageTitle = 'Notifications';
$donorId = current_user()['user_id'];

$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$donorId]);

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
$stmt->execute([$donorId]);
$notifications = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Notifications</h1>

<?php if (empty($notifications)): ?>
    <div class="empty-state">No notifications yet.</div>
<?php else: ?>
    <?php foreach ($notifications as $n): ?>
    <div class="card" style="margin-bottom:10px;">
        <div class="card-body" style="padding:14px 20px;">
            <p style="margin:0;"><?= clean($n['message']) ?></p>
            <span class="help-text"><?= formatDate($n['created_at']) ?> &bull; sent as <?= clean($n['type']) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
