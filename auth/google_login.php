<?php
/**
 * Step 1 of Google OAuth: redirect the user to Google's consent screen.
 */
session_start();
require_once __DIR__ . '/../config/oauth_config.php';

// CSRF protection for the OAuth flow
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'access_type'   => 'online',
    'prompt'        => 'select_account',
]);

header('Location: ' . GOOGLE_AUTH_URL . '?' . $params);
exit;
