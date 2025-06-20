<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bootstrap PDO and cart helpers
require_once __DIR__ . '/db.php';           
require_once __DIR__ . '/webfunctions.php'; 

// Require login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

//Load any existing cart from cookie
loadCartFromCookie();

//Add to cart
if (isset($_GET['add'])) {
    $productId = (int) $_GET['add'];
    if ($productId > 0) {
        $_SESSION['seshCart'][$productId] = ($_SESSION['seshCart'][$productId] ?? 0) + 1;
        saveCartToDB();
    }
    header("Location: cart.php");
    exit;
}

// Update quantity
if (isset($_GET['update_id'], $_GET['new_qty'])) {
    $pid = (int) $_GET['update_id'];
    $qty = (int) $_GET['new_qty'];

    if ($qty <= 0) {
        unset($_SESSION['seshCart'][$pid]);
    } else {
        $_SESSION['seshCart'][$pid] = $qty;
    }
    saveCartToDB();
    header("Location: cart.php");
    exit;
}

//Clear cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all'])) {
    clearCart(true);
    header("Location: cart.php");
    exit;
}

// Build items array and total
$cartItems = [];
$totalCost = 0.0;

if (!empty($_SESSION['seshCart'])) {
    $ids = array_keys($_SESSION['seshCart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Fetch product details
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $pid  = (int) $row['id'];
        $name  = $row['name'];
        $price = (float) $row['price'];
        $qty   = (int) ($_SESSION['seshCart'][$pid] ?? 0);
        $lineTotal = $price * $qty;

        $totalCost += $lineTotal;
        $cartItems[] = [
            'id'  => $pid,
            'name' => $name,
            'price' => $price,
            'quantity' => $qty,
            'lineTotal' => $lineTotal,
        ];
    }
}

//Render
$hideSearch = false;
require_once __DIR__ . '/header.php';
?>

<div class="max-w-2xl mx-auto px-4 py-8">
  <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Your Cart</h2>

  <?php if (empty($cartItems)): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
      <p class="text-gray-500 text-lg mb-4">Your cart is empty.</p>
      <button onclick="window.location.href='index.php';"
              class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
        Browse Products
      </button>
    </div>
  <?php else: ?>
    <form method="post" class="space-y-4 mb-6">
      <?php foreach ($cartItems as $item): ?>
        <div class="flex items-center justify-between bg-white rounded-lg shadow border border-gray-200 p-4">
          <div>
            <h3 class="font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
            <p class="text-gray-500">R<?= number_format($item['price'], 2) ?> each</p>
          </div>

          <div class="flex items-center space-x-2">
            <button formaction="cart.php?update_id=<?= $item['id'] ?>&new_qty=<?= $item['quantity'] - 1 ?>"
                    class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">−</button>
            <span class="px-3 py-1 font-medium"><?= $item['quantity'] ?></span>
            <button formaction="cart.php?update_id=<?= $item['id'] ?>&new_qty=<?= $item['quantity'] + 1 ?>"
                    class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">+</button>
          </div>

          <div class="text-right min-w-[80px]">
            <p class="font-semibold">R<?= number_format($item['lineTotal'], 2) ?></p>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="flex justify-between items-center border-t border-gray-200 pt-4">
        <span class="text-xl font-semibold">Total:</span>
        <span class="text-xl font-bold">R<?= number_format($totalCost, 2) ?></span>
      </div>

      <div class="flex justify-between mt-6">
        <button type="button" onclick="window.location.href='index.php';"
                class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-medium transition-colors">
          Return to Shop
        </button>
        <button type="button" onclick="window.location.href='checkout.php';"
                class="bg-[#30cfd0] hover:bg-[#29b8ba] text-white px-6 py-2 rounded-lg font-medium transition-colors">
          Proceed to Checkout
        </button>
      </div>

      <div class="text-center mt-4">
        <button type="submit" name="clear_all"
                class="text-[#30cfd0] hover:underline font-medium">
          Clear Cart
        </button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
