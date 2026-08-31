<?php
/**
 * POST /hopehub/payment/verify_payment.php
 *
 * Called via fetch() from checkout.php's Razorpay handler callback. This is
 * the step that actually confirms a payment is real — never trust the
 * client-side "handler" callback alone, since a malicious user could call
 * your success endpoint directly without ever paying. Three checks happen
 * here, all server-to-server:
 *
 *   1. The donation belongs to the logged-in donor and is still pending
 *   2. The razorpay_order_id returned matches the one WE created for this
 *      exact donation (stops someone reusing a valid signature from a
 *      completely different payment)
 *   3. The HMAC signature is valid for that order_id + payment_id, proving
 *      Razorpay itself generated it (only Razorpay and us know the secret)
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify.php';
require_once __DIR__ . '/razorpay_helper.php';
require_donor();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

$donorId       = current_user()['user_id'];
$donationId    = (int)($input['donation_id'] ?? 0);
$paymentId     = $input['razorpay_payment_id'] ?? '';
$orderId       = $input['razorpay_order_id'] ?? '';
$signature     = $input['razorpay_signature'] ?? '';

$stmt = $pdo->prepare(
    "SELECT * FROM donations WHERE donation_id = ? AND donor_id = ? AND donation_type = 'Cash' AND status = 'pending'"
);
$stmt->execute([$donationId, $donorId]);
$donation = $stmt->fetch();

if (!$donation) {
    echo json_encode(['success' => false, 'error' => 'Donation not found or already processed.']);
    exit;
}

// Check 2: the order_id must match the one we created for THIS donation.
if (empty($donation['razorpay_order_id']) || $donation['razorpay_order_id'] !== $orderId) {
    echo json_encode(['success' => false, 'error' => 'Order mismatch — this payment does not belong to this donation.']);
    exit;
}

// Check 3: verify the cryptographic signature.
if (!$paymentId || !$signature || !razorpayVerifySignature($orderId, $paymentId, $signature)) {
    $pdo->prepare("UPDATE donations SET status = 'failed' WHERE donation_id = ?")->execute([$donationId]);
    echo json_encode(['success' => false, 'error' => 'Payment signature could not be verified.']);
    exit;
}

// Extra server-to-server confirmation + fetch the real payment method used.
$method = 'razorpay';
$fetchResult = razorpayFetchPayment($paymentId);
if ($fetchResult['success'] && !empty($fetchResult['payment']['method'])) {
    $method = $fetchResult['payment']['method'];
}

$pdo->beginTransaction();
try {
    $pdo->prepare(
        "INSERT INTO payments (donation_id, method, transaction_ref, status) VALUES (?, ?, ?, 'success')"
    )->execute([$donationId, $method, $paymentId]);

    $pdo->prepare("UPDATE donations SET status = 'success' WHERE donation_id = ?")->execute([$donationId]);
    $pdo->prepare("INSERT INTO receipts (donation_id) VALUES (?)")->execute([$donationId]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Could not save the confirmed payment. Please contact support.']);
    exit;
}

sendNotification(
    $pdo, $donorId,
    "Payment of " . formatCurrency($donation['amount']) . " confirmed via Razorpay ({$method}). Payment ID: {$paymentId}. Your receipt is ready.",
    'email'
);
flash('success', 'Payment successful! Your receipt is ready to download.');

echo json_encode(['success' => true]);
