<?php
require_once 'webfunctions.php';
$currentPage = basename($_SERVER['PHP_SELF'], ".php");
?>
</main>

<nav class="fixed bottom-0 left-0 right-0 bg-[#e0f7f7] border-t border-[#cdeaea] z-50">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-around items-center py-2">

      <!-- Home -->

      <?php $homeActive = ($currentPage === 'index'); ?>
      <button onclick="window.location.href='index.php';" class="flex flex-col items-center space-y-1 group">
        <i data-lucide="home" class="w-6 h-6 <?php echo $homeActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>"></i>
        <span class="text-xs <?php echo $homeActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>">Home</span>
      </button>

      <!-- Cart -->
      <?php $cartActive = in_array($currentPage, ['cart_pg', 'checkout_pg', 'purchased_pg']); ?>
      <button onclick="window.location.href='cart.php';" class="flex flex-col items-center space-y-1 group">
        <i data-lucide="shopping-cart" class="w-6 h-6 <?php echo $cartActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>"></i>
        <span class="text-xs <?php echo $cartActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>">Cart</span>
      </button>

      <!-- Profile -->

      <?php $profileActive = ($currentPage === 'profile_pg'); ?>
      <?php if (isLoggedIn()): ?>
        <button onclick="window.location.href='account.php';" class="flex flex-col items-center space-y-1 group">
         <i data-lucide="user-circle" class="w-6 h-6 <?php echo $profileActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>"></i>
          <span class="text-xs <?php echo $profileActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>">Profile</span>
        </button>

        <!-- Admin -->
        <?php if (isset($_SESSION['SeshKey']['is_admin']) && $_SESSION['SeshKey']['is_admin'] == 1): ?>
          <?php $adminActive = ($currentPage === 'admin_panel'); ?>
          <button onclick="window.location.href='admin.php';" class="flex flex-col items-center space-y-1 group">
            <i data-lucide="shield-check" class="w-6 h-6 <?php echo $adminActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>"></i>
            <span class="text-xs <?php echo $adminActive ? 'text-cyan-600' : 'text-black group-hover:text-cyan-600'; ?>">Admin</span>
          </button>
        <?php endif; ?>

      <?php else: ?>
        <button onclick="window.location.href='login.php';" class="flex flex-col items-center space-y-1 group">
          <i data-lucide="log-in" class="w-6 h-6 text-black group-hover:text-cyan-600"></i>
          <span class="text-xs text-black group-hover:text-cyan-600">Profile</span>
        </button>
      <?php endif; ?>

    </div>
  </div>
</nav>

<script src="https://unpkg.com/lucide/dist/lucide.iife.min.js"></script>
<script>
  lucide.createIcons();

  // removes  the ability to right click in the website
  document.addEventListener('contextmenu', e => e.preventDefault());
</script>
</body>
</html>
