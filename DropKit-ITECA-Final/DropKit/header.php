<?php
require_once 'webfunctions.php';  
loadCartFromCookie();

$showSearch   = !($hideSearch ?? false);
$loggedInUser = getLoggedInUsername();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DropKit</title>
  <!-- Tailwind style for  CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- use Lucide icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">

<header class="bg-[#e0f7f7] shadow-sm border-b border-[#cdeaea] sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
    
    <!-- logo for dropkit -->
    <div class="flex items-center space-x-2 cursor-pointer" onclick="window.location.href='index.php';">
      <span class="text-2xl font-semibold text-gray-900">DropKit</span>
    </div>

    <!-- searchbar -->
    <?php if ($showSearch): ?>
      <div class="w-full max-w-lg mx-4">
        <label for="searchInput" class="sr-only">Search products</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
          </div>
          <input id="searchInput" type="text" placeholder="Search products..."
                 class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full 
                        bg-white placeholder-gray-500 focus:outline-none focus:ring-2 
                        focus:ring-cyan-500 focus:border-transparent transition-all" />
        </div>
      </div>
    <?php endif; ?>

    <!-- user icon  -->
    <div class="flex items-center space-x-4">
      <?php if ($loggedInUser): ?>
       <span class="text-gray-700">
          Hello, <strong><?php echo htmlspecialchars($loggedInUser); ?></strong>
       <?php if (!empty($_SESSION['SeshKey']['is_admin']) && $_SESSION['SeshKey']['is_admin'] == 1): ?>
         <span class="text-xs font-semibold text-cyan-600">(Admin)</span>
       <?php endif; ?>
        </span>

        <button onclick="window.location.href='account.php';"
                class="p-2 rounded-full hover:bg-gray-100 transition-colors">
          <i data-lucide="user-round" class="h-6 w-6 text-gray-600"></i>
        </button>
      <?php else: ?>
        <button onclick="window.location.href='login.php';" 
                class="text-gray-600 hover:text-gray-800 transition-colors">Log In</button>
      <?php endif; ?>
    </div>
  </div>

  <!-- filter for the categories -->
  <?php if ($showSearch): ?>
    <div class="bg-[#e0f7f7] py-2 border-b border-[#cdeaea]">
      <div class="max-w-7xl mx-auto px-4 flex justify-center space-x-2 overflow-x-auto">
        <?php
          $categories = ['All','Wallpapers','Templates','Fonts'];
          $activeCat   = $_GET['category'] ?? 'All';
        ?>
        <?php foreach ($categories as $cat): ?>
          <?php 
            $isActive  = ($activeCat === $cat);
            $btnClass  = $isActive 
                       ? 'px-4 py-2 rounded-full text-sm font-medium 
                          bg-cyan-600 text-white border border-cyan-600'
                       : 'px-4 py-2 rounded-full text-sm font-medium 
                          bg-cyan-100 text-cyan-700 border border-cyan-300 hover:bg-cyan-200';
          ?>
          <button onclick="window.location.href='index.php?category=<?php echo $cat; ?>';"
                  class="<?php echo $btnClass; ?>">
            <?php echo $cat; ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</header>

<!-- Lucide style -->
<script>
  lucide.createIcons();
  document.addEventListener('contextmenu', e => e.preventDefault());
</script>

<main class="flex-grow">
