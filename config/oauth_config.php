<?php
/**
 * Google OAuth 2.0 configuration.
 *
 * Setup (free, ~5 minutes):
 *   1. Go to https://console.cloud.google.com/apis/credentials
 *   2. Create an "OAuth client ID" -> Application type: Web application
 *   3. Under "Authorized redirect URIs" add the exact value of GOOGLE_REDIRECT_URI below
 *      (change host/path if your local URL is different).
 *   4. Copy the generated Client ID and Client Secret into the constants below.
 *
 * No composer / SDK required — auth/google_login.php and auth/google_callback.php
 * talk to Google's OAuth endpoints directly over HTTPS.
 */
define('GOOGLE_CLIENT_ID', '793763009018-2cp7a30om2dhudlatafrqsv265ukb0mk.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/hopehub/auth/google_callback.php');

define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v3/userinfo');
