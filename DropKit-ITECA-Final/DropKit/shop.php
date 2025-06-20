<?php
session_start();
require_once 'webfunctions.php';
require_once 'db.php';

loadCartFromCookie();

// need login to go to shop
if (!isset($_SESSION['SeshKey'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>DropKit | Browse</title>
  <link rel="stylesheet" href="style.css">
  <style>.modal { background-color: white; }</style>
</head>
<body>

<!-- header account icon-->
<div class="top-right">
  <a href="account.php">
    <img src="assets/icons/Account-icon.png" class="icon-btn" alt="Account">
  </a>
</div>

<h1 style="text-align:center; margin-top:20px;">All Products</h1>

<!--search and category filtering -->
<div class="search-filter-box">
  <input type="text"
         class="form-control"
         id="searchInput"
         placeholder="Search products..."
         onkeyup="searchProducts()">
  <div class="categories">
    <button onclick="filterCategory('Wallpapers')" class="filter-btn">Wallpapers</button>
    <button onclick="filterCategory('Templates')" class="filter-btn">Templates</button>
    <button onclick="filterCategory('Fonts')" class="filter-btn">Fonts</button>
  </div>
</div>

<!-- Product-->
<div class="product-grid" id="productGrid">
  <?php
  $result = $conn->query("SELECT * FROM products");
  while ($row = $result->fetch_assoc()) {
      $imgPath = (
          $row['category'] === 'Wallpapers'
              ? htmlspecialchars($row['image'])
              : (
                  $row['category'] === 'Templates'
                      ? "assets/ProductPlaceholder/Templates.jpg"
                      : "assets/ProductPlaceholder/Fonts.jpg"
                )
      );

      echo '
        <div class="product-card"
             data-category="' . htmlspecialchars($row['category']) . '"
             data-name="' . strtolower(htmlspecialchars($row['name'])) . '"
             onclick=\'openModal(' . json_encode($row) . ')\' >
            <img class="product-img" src="' . $imgPath . '" alt="' . htmlspecialchars($row['name']) . '">
            <h3>' . htmlspecialchars($row['name']) . '</h3>
        </div>';
  }
  ?>
</div>

<!-- popup prooduct-->
<div id="productModal" class="modal-overlay" onclick="closeModal(event)">
  <div class="modal" onclick="event.stopPropagation();">
    <button class="close-btn" onclick="closeModal()">×</button>
    <img id="modalImg" src="" alt="">
    <h3 id="modalTitle"></h3>
    <p id="modalDesc"></p>
    <p id="modalPrice"></p>
    <input type="hidden" id="modalProductId">
    <a id="modalAddToCart" class="btn-primary">Add to Cart</a>
  </div>
</div>

<!-- bottom bar -->
<div class="bottom-nav">
  <a href="index.php">
    <img src="assets/icons/Home-icon.jpg" class="nav-icon" alt="Home">
    <span>Home</span>
  </a>
  <a href="#productGrid">
    <img src="assets/icons/category-icon.jpg" class="nav-icon" alt="Categories">
    <span>Categories</span>
  </a>
  <a href="cart.php">
    <img src="assets/icons/Cart-icon.jpg" class="nav-icon" alt="Cart">
    <span>Cart</span>
  </a>
  <a href="account.php">
    <img src="assets/icons/Account-icon.jpg" class="nav-icon" alt="Account">
    <span>Account</span>
  </a>
</div>

<!-- popup and filters -->
<script>
function openModal(product) {
  document.getElementById("modalImg").src = product.image;
  document.getElementById("modalTitle").innerText = product.name;
  document.getElementById("modalDesc").innerText = product.description;
  document.getElementById("modalPrice").innerText = "Price: R" + parseFloat(product.price).toFixed(2);
  document.getElementById("modalProductId").value = product.id;
  document.getElementById("productModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("productModal").style.display = "none";
}

function filterCategory(cat) {
  document.querySelectorAll(".product-card").forEach(card => {
    const c = card.getAttribute("data-category");
    card.style.display = (cat === c || cat === "All") ? "block" : "none";
  });
}

function searchProducts() {
  const val = document.getElementById("searchInput").value.toLowerCase();
  document.querySelectorAll(".product-card").forEach(card => {
    const name = card.getAttribute("data-name");
    card.style.display = name.includes(val) ? "block" : "none";
  });
}

document.getElementById("modalAddToCart").onclick = function () {
  const pid = document.getElementById("modalProductId").value;
  window.location.href = "cart_pg.php?add=" + pid;
};
</script>
</body>
</html>
