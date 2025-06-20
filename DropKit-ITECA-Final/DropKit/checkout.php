<?php
//Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bootstrap your PDO connection and cart helpers
require_once __DIR__ . '/db.php';// defines $pdo
require_once __DIR__ . '/webfunctions.php';// starts session, defines cart/user helpers

//Require login
if (! isLoggedIn()) {
    header('Location: login.php');
    exit;
}

//Load any existing cart from cookie
loadCartFromCookie();

// Build items array and compute total
$cartItems = [];
$totalCost = 0.0;

if (! empty($_SESSION['seshCart'])) {
    $ids = array_keys($_SESSION['seshCart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Fetch product details via PDO
    $stmt = $pdo->prepare("
        SELECT id, name, price
        FROM products
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $pid  = (int) $row['id'];
        $name = $row['name'];
        $price = (float) $row['price'];
        $quantity = (int) ($_SESSION['seshCart'][$pid] ?? 0);
        $lineTotal = $price * $quantity;
        $totalCost += $lineTotal;

        $cartItems[] = [
            'id' => $pid,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'lineTotal' => $lineTotal,
        ];
    }
}

// Handle purchas confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_purchase'])) {
    $userId = getLoggedInUserId();

    if (! empty($cartItems)) {
        $stmt = $pdo->prepare("
            INSERT INTO purchases (user_id, product_id, quantity)
            VALUES (?, ?, ?)
        ");
        foreach ($cartItems as $item) {
            $stmt->execute([
                $userId,
                $item['id'],
                $item['quantity'],
            ]);
        }
    }

    // Clear cart from session cookie and DB
    clearCart(true);

    // Redirect to a purchased page
    header('Location: purchased_pg.php');
    exit;
}

// Render page
$hideSearch = false;
require_once __DIR__ . '/header.php';
?>

<div class="flex justify-center py-12 px-4">
  <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8 w-full max-w-lg">
    <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Checkout</h2>

    <?php if (empty($cartItems)): ?>
      <p class="text-center text-gray-500">
        Your cart is empty.
        <a href="index.php" class="text-purple-600 hover:underline">Go back to shop</a>.
      </p>
    <?php else: ?>
      <ul class="divide-y divide-gray-200 mb-6">
        <?php foreach ($cartItems as $item): ?>
          <li class="flex justify-between py-3">
            <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
            <span class="font-semibold">R<?= number_format($item['lineTotal'], 2) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="flex justify-between items-center border-t border-gray-200 pt-4 mb-6">
        <span class="text-xl font-semibold">Total:</span>
        <span class="text-xl font-bold">R<?= number_format($totalCost, 2) ?></span>
      </div>

      <form method="POST" class="text-center">
        <button type="submit" name="confirm_purchase"
                class="w-full bg-[#30cfd0] hover:bg-[#29b8ba] text-white px-6 py-3 rounded-lg font-medium transition-colors">
          Confirm Purchase
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
