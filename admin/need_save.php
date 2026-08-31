<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['mark_fulfilled']) && !empty($_POST['need_id'])) {
        $stmt = $pdo->prepare("UPDATE needs SET status='fulfilled' WHERE need_id = ?");
        $stmt->execute([(int)$_POST['need_id']]);
        flash('success', 'Need marked as fulfilled.');
    } else {
        $itemName = trim($_POST['item_name'] ?? '');
        $category = $_POST['category'] ?? 'Other';
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($itemName !== '') {
            $stmt = $pdo->prepare(
                "INSERT INTO needs (item_name, category, quantity) VALUES (?, ?, ?)"
            );
            $stmt->execute([$itemName, $category, $quantity]);
            flash('success', 'Need posted.');
        } else {
            flash('error', 'Please provide an item name.');
        }
    }
}
redirect('/hopehub/admin/manage_content.php');
