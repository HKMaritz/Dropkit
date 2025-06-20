<?php
//Full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//  Bootstrap: DB + helper functions
require_once __DIR__ . '/db.php'; // defines $pdo
require_once __DIR__ . '/webfunctions.php';//defines loadCartFromCookie


loadCartFromCookie();

// Build & execute products query
$categoryFilter = $_GET['category'] ?? 'All';

$sql    = "SELECT id, name, description, price, image, category FROM products";
$params = [];

if (in_array($categoryFilter, ['Wallpapers','Templates','Fonts'], true)) {
    $sql    .= " WHERE category = ?";
    $params[] = $categoryFilter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();// uses FETCH ASSOC by default from your db.php

//  Render page
$hideSearch = false;
require_once __DIR__ . '/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-6">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php foreach ($products as $prod): 
      // pick correct image
      if ($prod['category'] === 'Wallpapers') {
        $imgSrc = htmlspecialchars($prod['image']);
      } elseif ($prod['category'] === 'Templates') {
        $imgSrc = 'assets/ProductPlaceholder/Templates.jpg';
      } else {
        $imgSrc = 'assets/ProductPlaceholder/Fonts.jpg';
      }
    ?>
      <div
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg cursor-pointer transition-all group"
        data-id="<?= $prod['id'] ?>"
        data-name="<?= htmlspecialchars($prod['name']) ?>"
        data-description="<?= htmlspecialchars($prod['description']) ?>"
        data-price="<?= number_format($prod['price'],2) ?>"
        data-image="<?= $imgSrc ?>"
        onclick="openModal(this)"
      >
        <img
          src="<?= $imgSrc ?>"
          alt="<?= htmlspecialchars($prod['name']) ?>"
          class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300"
        />
        <div class="p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-1"><?= htmlspecialchars($prod['name']) ?></h3>
          <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($prod['description']) ?></p>
          <div class="flex items-center justify-between">
            <span class="text-xl font-bold text-gray-900">R<?= number_format($prod['price'],2) ?></span>
            <button
              onclick="event.stopPropagation(); window.location.href='cart.php?add=<?= $prod['id'] ?>';"
              class="bg-[#32c2c2] hover:bg-[#28b0b0] text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 hover:shadow-md"
            >
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- Product Modal -->
<div id="modalOverlay" class="fixed inset-0 bg-black/50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg max-w-md w-full mx-4 p-6 relative">
    <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl font-bold" onclick="closeModal()">×</button>
    <img id="modalImg" src="" alt="" class="w-full h-48 object-cover rounded-md mb-4" />
    <h3 id="modalName" class="text-2xl font-semibold text-gray-900 mb-2"></h3>
    <p id="modalDesc" class="text-gray-600 mb-4"></p>
    <p class="text-lg mb-4"><strong>Price:</strong> R<span id="modalPrice"></span></p>
    <div class="text-right">
      <a href="#" id="addToCartBtn" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
        Add to Cart
      </a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script>
// simple search filter
document.getElementById('searchInput')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('[data-name]').forEach(card => {
    card.style.display = card.dataset.name.toLowerCase().includes(q) ? 'block' : 'none';
  });
});

// modal open/close
function openModal(el) {
  document.getElementById('modalImg').src = el.dataset.image;
  document.getElementById('modalName').textContent = el.dataset.name;
  document.getElementById('modalDesc').textContent = el.dataset.description;
  document.getElementById('modalPrice').textContent = el.dataset.price;
  document.getElementById('addToCartBtn').href = `cart_pg.php?add=${encodeURIComponent(el.dataset.id)}`;
  const overlay = document.getElementById('modalOverlay');
  overlay.classList.remove('hidden');
  overlay.classList.add('flex');
}
function closeModal() {
  const overlay = document.getElementById('modalOverlay');
  overlay.classList.remove('flex');
  overlay.classList.add('hidden');
}
document.getElementById('modalOverlay').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeModal();
});
</script>
