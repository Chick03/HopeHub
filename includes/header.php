<?php
// Expects session already started by the including page.
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? clean($pageTitle) . ' - HopeHub' : 'HopeHub | Orphanage Donation Management' ?></title>
<link rel="stylesheet" href="/hopehub/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="/hopehub/index.php" class="logo">Hope<span>Hub</span></a>
        <nav class="main-nav">
            <a href="/hopehub/index.php#leaderboard">Top Donors</a>
            <a href="/hopehub/donor/donate.php">Donate</a>
            <?php if ($user && $user['role'] === 'donor'): ?>
                <a href="/hopehub/donor/dashboard.php">Dashboard</a>
                <a href="/hopehub/donor/donation_history.php">My Donations</a>
                <a href="/hopehub/donor/notifications.php">Notifications</a>
                <a href="/hopehub/auth/logout.php" class="btn btn-outline">Logout</a>
            <?php elseif ($user && $user['role'] === 'admin'): ?>
                <a href="/hopehub/admin/dashboard.php">Admin Dashboard</a>
                <a href="/hopehub/auth/logout.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="/hopehub/auth/login.php" class="btn btn-primary">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container page-content">
<?php
$successMsg = flash('success');
$errorMsg   = flash('error');
if ($successMsg): ?>
    <div class="alert alert-success"><?= clean($successMsg) ?></div>
<?php endif;
if ($errorMsg): ?>
    <div class="alert alert-error"><?= clean($errorMsg) ?></div>
<?php endif; ?>
