<?php
/**
 * Razorpay REST API helpers — plain cURL, no SDK/Composer dependency.
 */
require_once __DIR__ . '/../config/razorpay_config.php';

// Creates an order for the given rupee amount; Razorpay ties the Checkout
// widget to this order_id and won't let the paid amount be tampered with.
function razorpayCreateOrder(float $amountInRupees, string $receiptLabel): array {
    $ch = curl_init(RAZORPAY_API_BASE . '/orders');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_POSTFIELDS     => json_encode([
            'amount'          => (int) round($amountInRupees * 100), // paise
            'currency'        => 'INR',
            'receipt'         => $receiptLabel,
            'payment_capture' => 1, // auto-capture funds once authorized
        ]),
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'error' => 'Network error contacting Razorpay: ' . $curlError];
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200 || empty($data['id'])) {
        $message = $data['error']['description'] ?? 'Unknown error creating Razorpay order.';
        return ['success' => false, 'error' => $message];
    }

    return ['success' => true, 'order' => $data];
}

// HMAC-SHA256 of "order_id|payment_id" keyed with the secret must match
// what Razorpay sent — this is what actually proves a payment is genuine.
function razorpayVerifySignature(string $orderId, string $paymentId, string $signature): bool {
    $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
    return hash_equals($expected, $signature);
}

// Server-to-server confirmation + gets the real method used (card/upi/etc).
function razorpayFetchPayment(string $paymentId): array {
    $ch = curl_init(RAZORPAY_API_BASE . '/payments/' . urlencode($paymentId));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return ['success' => false];
    }

    $data = json_decode($response, true);
    return ['success' => true, 'payment' => $data];
}
