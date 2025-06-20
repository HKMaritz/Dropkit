<?php
// enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "STEP 1: PHP is running\n";

// now try to include your DB file
require __DIR__ . '/db.php';

echo "STEP 2: db.php loaded successfully\n";
