<?php
// Enable full error reporting

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Bootstrap PDO and auth/cart helpers
require_once __DIR__ . '/db.php';// defines $pdo
require_once __DIR__ . '/webfunctions.php';// starts session, defines isLoggedIn(), isAdmin()

//  Require login + admin
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
if (!isAdmin()) {
    exit('Access denied.');
}

//  Handle "Add Product"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name  = trim($_POST['name']        ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price']   ?? 0);
    $category = trim($_POST['category']    ?? '');
    $image = trim($_POST['image']       ?? '');
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

//Handle "Delete Product"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $productId = (int) $_POST['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$productId]);
}

// Fetch data for display
$users  = $pdo->query("SELECT id, username, email FROM users ORDER BY id DESC")
                ->fetchAll(PDO::FETCH_ASSOC);
$orders = $pdo->query("
    SELECT
      p.id,
      u.username AS user,
      pr.name AS product,
      p.quantity
    FROM purchases p
    JOIN users u ON u.id = p.user_id
    JOIN products pr ON pr.id = p.product_id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT id, name, category, price FROM products ORDER BY id DESC")
                ->fetchAll(PDO::FETCH_ASSOC);

//Render
$hideSearch = true;
require_once __DIR__ . '/header.php';
?>
<div class="max-w-5xl mx-auto py-10 px-4">
  <h1 class="text-3xl font-bold mb-6 text-center">Admin Dashboard</h1>

  <div class="flex justify-center mb-6 space-x-4">
    <button onclick="showSection('addProduct')" class="filter-btn">Add Product</button>
    <button onclick="showSection('deleteProduct')" class="filter-btn">Delete Product</button>
    <button onclick="showSection('viewUsers')" class="filter-btn">Users</button>
    <button onclick="showSection('viewOrders')" class="filter-btn">Orders</button>
  </div>

  <!--Add Product -->
  <div id="addProduct" class="admin-section hidden max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-semibold mb-4 text-center">Add New Product</h2>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="add_product" value="1">
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
        <input type="text" name="image" required placeholder="assets/images/products/preview.jpg" class="form-control w-full">
      </div>
      <div>
        <label class="block mb-1 font-medium">
          Download File Path <span class="text-sm text-gray-500">(optional)</span>
        </label>
        <input type="text" name="file_path" placeholder="assets/downloads/file.zip" class="form-control w-full">
      </div>
      <button type="submit" class="bg-[#30cfd0] hover:bg-[#29b8ba] text-white px-6 py-2 rounded transition">
        Add Product
      </button>
    </form>
  </div>

  <!--Delete Product -->
  <div id="deleteProduct" class="admin-section hidden">
    <h2 class="text-xl font-semibold mb-4">Delete Products</h2>
    <form method="POST">
      <table class="w-full text-left border">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
             <td><?= $p['id'] ?></td>
             <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['category']) ?></td>
              <td>R<?= number_format($p['price'], 2) ?></td>
            <td>
                <button type="submit" name="delete_product" value="<?= $p['id'] ?>"
                  onclick="return confirm('Are you sure you want to delete this product?');"
                  class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                  Delete
                </button>
              </td>
           </tr>
          <?php endforeach; ?>
       </tbody>
     </table>
    </form>
  </div>

  <!-- View Users -->
  <div id="viewUsers" class="admin-section hidden">
    <h2 class="text-xl font-semibold mb-4">All Users</h2>
    <table class="w-full text-left border">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th></tr></thead>
     <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
          <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
          </tr>
        <?php endforeach; ?>
     </tbody>
    </table>
  </div>

  <!-- View Orders -->
  <div id="viewOrders" class="admin-section hidden">
    <h2 class="text-xl font-semibold mb-4">Recent Orders</h2>
    <table class="w-full text-left border">
      <thead><tr><th>ID</th><th>User</th><th>Product</th><th>Qty</th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= $o['id'] ?></td>
            <td><?= htmlspecialchars($o['user']) ?></td>
            <td><?= htmlspecialchars($o['product']) ?></td>
            <td><?= $o['quantity'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function showSection(id) {
  document.querySelectorAll('.admin-section').forEach(s => s.classList.add('hidden'));
  document.getElementById(id).classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
