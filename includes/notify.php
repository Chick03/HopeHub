<?php
/**
 * Notification Module.
 * This is a self-contained SIMULATED email/SMS sender: it logs the
 * notification into the `notifications` table (and to a local log file)
 * instead of calling a real mail/SMS API, exactly like the Payment Module
 * simulates the payment gateway. Swap sendNotification()'s body for
 * PHPMailer / an SMS API if you want it to send for real.
 */

function sendNotification(PDO $pdo, int $userId, string $message, string $type = 'email') {
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)"
    );
    $stmt->execute([$userId, $message, $type]);

    $line = sprintf("[%s] (%s) to user #%d: %s\n", date('Y-m-d H:i:s'), strtoupper($type), $userId, $message);
    @file_put_contents(__DIR__ . '/../notifications.log', $line, FILE_APPEND);
}
