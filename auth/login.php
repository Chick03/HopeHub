<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (is_logged_in()) {
    $u = current_user();
    redirect($u['role'] === 'admin' ? '/hopehub/admin/dashboard.php' : '/hopehub/donor/dashboard.php');
}

$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
?>
<div class="form-card" style="text-align:center;">
    <h2>Welcome to HopeHub</h2>
    <p class="help-text" style="margin-bottom:24px;">
        Sign in securely with your Google account. Donor and Admin access
        is determined automatically from your account &mdash; no separate
        passwords to manage.
    </p>
    <a href="/hopehub/auth/google_login.php" class="btn btn-primary btn-block">
        Continue with Google
    </a>
    <p class="help-text" style="margin-top:20px;">
        New here? Signing in with Google creates your Donor account automatically.
    </p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
