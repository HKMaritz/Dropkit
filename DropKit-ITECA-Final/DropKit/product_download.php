<?php

 //Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Bootstrap PDO and auth helpers
require_once __DIR__ . '/db.php';           
require_once __DIR__ . '/webfunctions.php'; 

//Block unauthenticated users
if (! isLoggedIn()) {
    http_response_code(403);
    exit('Not authorized.');
}

// Get user andproduct IDs
$userId    = getLoggedInUserId();
$productId = (int) ($_GET['id'] ?? 0);

// Verify purchase and fetch download path
$stmt = $pdo->prepare("
    SELECT pr.download_path
    FROM purchases AS p
    JOIN products  AS pr ON p.product_id = pr.id
    WHERE p.user_id = ? AND p.product_id = ?
    LIMIT 1
");
$stmt->execute([$userId, $productId]);
$downloadPath = $stmt->fetchColumn();

// If path found serve the file
if ($downloadPath) {
    // Sanitize relative path
    $safePath = str_replace(['..', '\\'], '', $downloadPath);
    $fullPath = __DIR__ . '/' . $safePath;

    if (file_exists($fullPath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($safePath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}

// Fallback: not found or not purchased
http_response_code(404);
echo 'File not found or you are not authorized to download this item.';
