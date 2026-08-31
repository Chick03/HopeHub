<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orphanId = (int)($_POST['orphan_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM orphans WHERE orphan_id = ?");
    $stmt->execute([$orphanId]);
    flash('success', 'Child record removed.');
}
redirect('/hopehub/admin/manage_content.php');
