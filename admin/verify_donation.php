<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donationId = (int)($_POST['donation_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM donations WHERE donation_id = ? AND status = 'pending'");
    $stmt->execute([$donationId]);
    $donation = $stmt->fetch();

    if ($donation) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE donations SET status = 'verified' WHERE donation_id = ?")->execute([$donationId]);
            $pdo->prepare("INSERT INTO receipts (donation_id) VALUES (?)")->execute([$donationId]);
            $pdo->commit();

            sendNotification(
                $pdo, $donation['donor_id'],
                "Your {$donation['donation_type']} donation has been verified. Thank you! Your receipt is ready.",
                'email'
            );
            flash('success', 'Donation verified and receipt generated.');
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', 'Could not verify donation. Please try again.');
        }
    }
}
redirect('/hopehub/admin/verify_donations.php');
