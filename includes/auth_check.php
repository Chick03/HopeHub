<?php
/**
 * Session guards for role-based access control.
 * Include AFTER session_start() and AFTER config/db.php.
 */

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('/hopehub/auth/login.php');
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        die("Access denied. This page is for {$role}s only.");
    }
}

function require_donor() {
    require_role('donor');
}

function require_admin() {
    require_role('admin');
}
