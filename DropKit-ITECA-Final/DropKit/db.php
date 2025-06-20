<?php
//Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//InfinityFree MySQL credentials
define('DB_HOST', 'sql103.infinityfree.com');
define('DB_NAME', 'if0_39179348_dropkit');
define('DB_USER', 'if0_39179348');
define('DB_PASS', 'RlEVtTpKkJCf');

// Create the PDO instance
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Connection failed: display error and stop
    die("❌ Database connection failed: " . $e->getMessage());
}
