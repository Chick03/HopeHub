<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify.php';
require_donor();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hopehub/donor/donate.php');
}

$donorId      = current_user()['user_id'];
$donationType = $_POST['donation_type'] ?? '';
$needId       = !empty($_POST['need_id']) ? (int)$_POST['need_id'] : null;
$amount       = $donationType === 'Cash' ? (float)($_POST['amount'] ?? 0) : 0;
$quantity     = $donationType !== 'Cash' ? trim($_POST['quantity'] ?? '') : null;
$notes        = trim($_POST['notes'] ?? '');

$validTypes = ['Cash', 'Food', 'Clothes', 'Books', 'Medical'];
if (!in_array($donationType, $validTypes, true)) {
    flash('error', 'Invalid donation details. Please try again.');
    redirect('/hopehub/donor/donate.php');
}
if ($donationType === 'Cash' && $amount <= 0) {
    flash('error', 'Please enter a valid donation amount.');
    redirect('/hopehub/donor/donate.php');
}
if ($donationType !== 'Cash' && $quantity === '') {
    flash('error', 'Please describe what you are donating.');
    redirect('/hopehub/donor/donate.php');
}

$stmt = $pdo->prepare(
    "INSERT INTO donations (donor_id, need_id, donation_type, amount, quantity, notes, status)
     VALUES (?, ?, ?, ?, ?, ?, 'pending')"
);
$stmt->execute([$donorId, $needId, $donationType, $amount, $quantity, $notes]);
$donationId = $pdo->lastInsertId();

if ($donationType === 'Cash') {
    // Cash donations go through Razorpay Checkout.
    redirect('/hopehub/payment/checkout.php?donation_id=' . $donationId);
} else {
    // In-kind donations wait for an admin to verify physical receipt.
    sendNotification(
        $pdo, $donorId,
        "Your donation ($donationType) has been submitted and is pending verification.",
        'email'
    );
    flash('success', 'Donation submitted! It will be marked verified once we confirm receipt.');
    redirect('/hopehub/donor/donation_history.php');
}
