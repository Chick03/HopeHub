<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/SimplePDF.php';
require_login();

$donationId = (int)($_GET['donation_id'] ?? 0);
$user = current_user();

$stmt = $pdo->prepare(
    "SELECT d.*, u.name AS donor_name, u.email AS donor_email,
            r.receipt_id, r.issue_date, p.method, p.transaction_ref
     FROM donations d
     JOIN users u ON u.user_id = d.donor_id
     LEFT JOIN receipts r ON r.donation_id = d.donation_id
     LEFT JOIN payments p ON p.donation_id = d.donation_id AND p.status = 'success'
     WHERE d.donation_id = ?"
);
$stmt->execute([$donationId]);
$d = $stmt->fetch();

if (!$d || !$d['receipt_id']) { die('Receipt not available.'); }

// A donor may only download their own receipt; an admin may download any.
if ($user['role'] !== 'admin' && (int)$d['donor_id'] !== (int)$user['user_id']) {
    http_response_code(403);
    die('Access denied.');
}

$org = getOrphanageProfile($pdo);

$pdf = new SimplePDF();
$pdf->addLine('HopeHub - Donation Receipt', 18);
$pdf->addSpacer(24);
$pdf->addLine('Receipt No: ' . $d['receipt_id'], 11);
$pdf->addLine('Issue Date: ' . date('d M Y, h:i A', strtotime($d['issue_date'])), 11);
$pdf->addSpacer(12);
$pdf->addLine('Donor: ' . $d['donor_name'] . ' (' . $d['donor_email'] . ')', 11);
$pdf->addLine('Issued By: ' . $org['name'], 11);
$pdf->addSpacer(12);
$pdf->addLine('Donation Type: ' . $d['donation_type'], 11);
if ($d['donation_type'] === 'Cash') {
    $pdf->addLine('Amount: Rs. ' . number_format((float)$d['amount'], 2), 12);
    $pdf->addLine('Payment Method: ' . ($d['method'] ?? '-'), 11);
    $pdf->addLine('Transaction Ref: ' . ($d['transaction_ref'] ?? '-'), 11);
} else {
    $pdf->addLine('Item / Quantity: ' . $d['quantity'], 12);
}
$pdf->addLine('Status: ' . strtoupper($d['status']), 12);
$pdf->addSpacer(24);
$pdf->addLine('Thank you for supporting ' . $org['name'] . ' through HopeHub.', 11);
$pdf->addLine('This receipt was generated automatically and is valid without a signature.', 9);

$pdf->stream('HopeHub_Receipt_' . $donationId . '.pdf');
