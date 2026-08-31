<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $age  = (int)($_POST['age'] ?? 0);
    $gender = in_array($_POST['gender'] ?? '', ['Male','Female','Other'], true) ? $_POST['gender'] : 'Other';

    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO orphans (name, age, gender) VALUES (?, ?, ?)");
        $stmt->execute([$name, $age, $gender]);
        flash('success', 'Child added.');
    } else {
        flash('error', 'Please provide a valid name.');
    }
}
redirect('/hopehub/admin/manage_content.php');
