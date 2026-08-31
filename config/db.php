<?php
/**
 * Database connection (PDO + MySQL)
 * Update these four constants to match your XAMPP/WAMP MySQL setup.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'hopehub');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Make sure MySQL is running and the 'hopehub' "
        . "database has been imported. (" . $e->getMessage() . ")");
}
