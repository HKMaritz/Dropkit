<?php

//Enable error reporting

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//bootstrap PDO and helpers
require_once __DIR__ . '/db.php';           
require_once __DIR__ . '/webfunctions.php'; 

//Require login
if (! isLoggedIn()) {
    header("Location: login.php");
    exit;
}

//Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    clearCart(false);
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

//  Get loged in user info
$userId   = getLoggedInUserId();
$username = getLoggedInUsername();
$isAdmin  = isAdmin();

//Handle the list a product form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product_user'])) {
    $name = trim($_POST['name']        ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price']    ?? 0);
    $category = trim($_POST['category']    ?? '');
    $image  = trim($_POST['image']       ?? '');
    $filePath = trim($_POST['file_path']   ?? '');

    $stmt = $pdo->prepare("
        INSERT INTO products
          (name, description, price, category, image, download_path)
        VALUES
          (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $name,
        $desc,
        $price,
        $category,
        $image,
        $filePath
    ]);
}

// Fetch this user’s past purchases
$stmt = $pdo->prepare("
    SELECT 
      pr.id AS product_id,
      pr.name AS name,
      pr.image AS image,
      pr.price AS price,
      pr.category AS category
    FROM purchases p
    JOIN products pr ON p.product_id = pr.id
    WHERE p.user_id = ?
    GROUP BY pr.id
");
$stmt->execute([$userId]);
$purchases = $stmt->fetchAll();

//Render
$hideSearch = true;
require_once __DIR__ . '/header.php';
?>

<div class="max-w-4xl mx-auto py-12 px-4">
  <h2 class="text-3xl font-bold mb-6">
    Welcome, <?= htmlspecialchars($username) ?>!
  </h2>

  <?php if (count($purchases) > 0): ?>
    <h3 class="text-xl font-semibold mb-4">Your Purchased Items</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($purchases as $item): 
        $imgSrc = (!empty($item['image']) && file_exists($item['image']))
          ? htmlspecialchars($item['image'])
          : (
              $item['category'] === 'Templates'
                ? 'assets/ProductPlaceholder/Templates.jpg'
                : 'assets/ProductPlaceholder/Fonts.jpg'
            );
      ?>
        <div class="bg-white border rounded-lg p-4 shadow-sm flex items-center space-x-4">
          <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['name']) ?>"
               class="w-16 h-16 object-cover rounded" />
          <div class="flex-grow">
            <div class="font-semibold"><?= htmlspecialchars($item['name']) ?></div>
            <div class="text-sm text-gray-600">
              R<?= number_format($item['price'], 2) ?>
            </div>
          </div>
          <a href="product_download.php?id=<?= $item['product_id'] ?>"
             class="bg-[#30cfd0] hover:bg-[#29b8ba] text-white px-4 py-2 rounded text-sm font-medium transition-colors">
            Download
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-gray-600">You haven’t purchased anything yet.</p>
  <?php endif; ?>
</div>

<!-- List a Product for Sale -->
<div class="max-w-xl mx-auto bg-white border rounded-lg shadow p-6 mb-12">
  <h3 class="text-xl font-semibold mb-4">List a Product for Sale</h3>
  <form method="post" class="space-y-4">
    <input type="hidden" name="add_product_user" value="1">
    <div>
      <label class="block mb-1 font-medium">Product Name</label>
      <input type="text" name="name" required class="form-control w-full">
    </div>
    <div>
      <label class="block mb-1 font-medium">Description</label>
      <textarea name="description" required class="form-control w-full resize-none"></textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 font-medium">Price (R)</label>
        <input type="number" step="0.01" name="price" required class="form-control w-full">
      </div>
      <div>
        <label class="block mb-1 font-medium">Category</label>
        <select name="category" required class="form-control w-full">
          <option value="">Choose Category</option>
          <option value="Wallpapers">Wallpapers</option>
          <option value="Templates">Templates</option>
          <option value="Fonts">Fonts</option>
        </select>
      </div>
    </div>
    <div>
      <label class="block mb-1 font-medium">Image Path</label>
      <input type="text" name="image" required class="form-control w-full">
    </div>
    <div>
      <label class="block mb-1 font-medium">
        Download File Path <span class="text-sm text-gray-500">(optional)</span>
      </label>
      <input type="text" name="file_path" class="form-control w-full">
    </div>
    <button type="submit"
            class="bg-[#30cfd0] hover:bg-[#29b8ba] text-white px-6 py-2 rounded transition">
      Add Product
    </button>
  </form>
</div>

<!-- Logout & Return to Shop -->
<div class="max-w-md mx-auto py-8 text-center">
  <form method="post">
    <button type="submit" name="logout"
            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium mr-2">
      Log Out
    </button>
    <button type="button" onclick="window.location.href='index.php';"
            class="bg-[#30cfd0] hover:bg-[#29b8ba] text-white px-6 py-2 rounded-lg font-medium transition-colors">
      Return to Shop
    </button>
  </form>
</div>

<div class="h-28"></div>

<?php if ($isAdmin): ?>
  <div style="position: fixed; bottom: 90px; right: 20px; z-index: 50;">
    <button onclick="location.href='admin.php';"
            class="bg-cyan-500 hover:bg-cyan-600 text-white px-5 py-2 rounded-full shadow-lg transition">
      Admin Dashboard
    </button>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
