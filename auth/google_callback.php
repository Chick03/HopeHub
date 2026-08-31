<?php
/**
 * Step 2 of Google OAuth: Google redirects back here with an auth code.
 * We exchange it for an access token, fetch the user's profile, then
 * find-or-create the local user record and start a session.
 * Role (donor/admin) is decided by the local `users.role` column, never
 * by anything Google sends — admins must be seeded into the database.
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/oauth_config.php';
require_once __DIR__ . '/../includes/functions.php';

function curlPost($url, $data) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        die('OAuth token request failed: ' . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($response, true);
}

function curlGet($url, $token) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        die('OAuth userinfo request failed: ' . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($response, true);
}

// --- Validate CSRF state ---
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    unset($_SESSION['oauth_state']);
    die('Invalid OAuth state. Please try logging in again.');
}
unset($_SESSION['oauth_state']);

if (isset($_GET['error'])) {
    redirect('/hopehub/auth/login.php');
}

if (!isset($_GET['code'])) {
    die('No authorization code received from Google.');
}

// --- Exchange code for access token ---
$tokenData = curlPost(GOOGLE_TOKEN_URL, [
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

if (empty($tokenData['access_token'])) {
    die('Could not obtain access token from Google. Check your GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET in config/oauth_config.php.');
}

// --- Fetch profile info ---
$profile = curlGet(GOOGLE_USERINFO_URL, $tokenData['access_token']);

if (empty($profile['email'])) {
    die('Could not retrieve profile info from Google.');
}

$googleId = $profile['sub'];
$email    = $profile['email'];
$name     = $profile['name'] ?? explode('@', $email)[0];

// --- Find or create local user ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
$stmt->execute([$email, $googleId]);
$user = $stmt->fetch();

if ($user) {
    $update = $pdo->prepare("UPDATE users SET google_id = ?, oauth_token = ?, name = ? WHERE user_id = ?");
    $update->execute([$googleId, $tokenData['access_token'], $name, $user['user_id']]);
} else {
    // New sign-ins default to the 'donor' role. Admin accounts must already
    // exist in the database (see database/hopehub.sql) with a matching email.
    $insert = $pdo->prepare(
        "INSERT INTO users (name, email, google_id, oauth_token, role) VALUES (?, ?, ?, ?, 'donor')"
    );
    $insert->execute([$name, $email, $googleId, $tokenData['access_token']]);
    $userId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}

if ($user['status'] === 'suspended') {
    die('Your account has been suspended. Please contact the HopeHub admin team.');
}

$_SESSION['user'] = [
    'user_id' => $user['user_id'],
    'name'    => $user['name'],
    'email'   => $user['email'],
    'role'    => $user['role'],
];

redirect($user['role'] === 'admin' ? '/hopehub/admin/dashboard.php' : '/hopehub/donor/dashboard.php');
