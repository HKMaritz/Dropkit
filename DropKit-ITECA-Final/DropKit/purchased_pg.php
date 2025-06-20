<?php

//  Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bootstrap your PDO connection and helpers
require_once __DIR__ . '/db.php';           
require_once __DIR__ . '/webfunctions.php'; 

// 3) Ensure user is logged in
if (! isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$userId = getLoggedInUserId();

// Record purchases in the database
if (! empty($_SESSION['seshCart'])) {
    $insertStmt = $pdo->prepare("
        INSERT INTO purchases (user_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");
    foreach ($_SESSION['seshCart'] as $productId => $qty) {
        // Insert one record per product with its quantity
        $insertStmt->execute([
            $userId,
            (int)$productId,
            (int)$qty
        ]);
    }
}

// Save current cart JSON into the users saved cart field
$encodedCart = json_encode($_SESSION['seshCart'] ?? []);
$updateStmt = $pdo->prepare("
    UPDATE users
    SET saved_cart = ?
    WHERE id = ?
");
$updateStmt->execute([
    $encodedCart,
    $userId
]);

// Clear cart from sessioncookie and database
clearCart(true);

// Render the thank-you page
$hideSearch = true;
require_once __DIR__ . '/header.php';
?>
<div class="max-w-lg mx-auto py-12 px-4">
  <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8 text-center">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">Thank You for Your Purchase!</h2>
    <p class="text-gray-700 mb-6">Your order has been placed successfully.</p>
    <a href="index.php"
       class="bg-[#30cfd0] hover:bg-[#29b8ba] text-white px-6 py-2 rounded-lg font-medium transition-colors">
       Return to Shop
    </a>
  </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
