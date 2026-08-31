<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate   = $_GET['end'] ?? date('Y-m-d');

$stmt = $pdo->prepare(
    "SELECT d.donation_id, d.created_at, u.name AS donor_name, u.email AS donor_email,
            d.donation_type, d.amount, d.quantity, d.status
     FROM donations d
     JOIN users u ON u.user_id = d.donor_id
     WHERE DATE(d.created_at) BETWEEN ? AND ?
     ORDER BY d.created_at"
);
$stmt->execute([$startDate, $endDate]);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="hopehub_report_' . $startDate . '_to_' . $endDate . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Donation ID', 'Date', 'Donor', 'Email', 'Type', 'Amount', 'Quantity', 'Status']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['donation_id'], $r['created_at'], $r['donor_name'], $r['donor_email'],
        $r['donation_type'], $r['amount'], $r['quantity'], $r['status'],
    ]);
}
fclose($out);
