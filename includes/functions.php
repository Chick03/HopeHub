<?php
/**
 * General-purpose helper functions used across HopeHub.
 */

function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return '₹' . number_format((float)$amount, 2);
}

function formatDate($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

function redirect($path) {
    header("Location: $path");
    exit;
}

function generateTransactionRef() {
    return 'TXN' . strtoupper(bin2hex(random_bytes(6)));
}

/**
 * This project manages exactly one orphanage, so its profile is always
 * the single row with id = 1 in orphanage_profile.
 */
function getOrphanageProfile(PDO $pdo) {
    static $cached = null;
    if ($cached === null) {
        $stmt = $pdo->query("SELECT * FROM orphanage_profile WHERE id = 1");
        $cached = $stmt->fetch();
    }
    return $cached;
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
