<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = ? AND role = 'donor'");
    $stmt->execute([$userId]);
    $current = $stmt->fetchColumn();

    if ($current !== false) {
        $newStatus = $current === 'active' ? 'suspended' : 'active';
        $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?")->execute([$newStatus, $userId]);
        flash('success', 'User status updated.');
    }
}
redirect('/hopehub/admin/manage_users.php');
