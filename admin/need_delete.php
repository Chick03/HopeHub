<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $needId = (int)($_POST['need_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM needs WHERE need_id = ?");
    $stmt->execute([$needId]);
    flash('success', 'Need deleted.');
}
redirect('/hopehub/admin/manage_content.php');
