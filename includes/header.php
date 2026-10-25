<?php
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && ($_SESSION['role'] ?? '') === 'admin';
$cartCount = 0;

if ($isLoggedIn) {
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $cartCount = $result['total'] ?? 0;
    } catch (Exception $e) {
        $cartCount = 0;
    }
}
?><!DOCTYPE html>
<html class="light" lang="ar">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>L'ESSENCE <?php echo isset($page_title) ? '| ' . $page_title : ''; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,700&family=Source+Sans+3:wght@300;400;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "background": "#fdf8f8",
                        "primary": "#0b0c0c",
                        "on-surface": "#1c1b1b",
                        "on-surface-variant": "#444748",
                        "outline-variant": "#c4c7c7",
                        "surface-container-low": "#f7f3ee",
                        "secondary": "#685c52",
                        "on-primary": "#ffffff",
                        "error": "#ba1a1a",
                        "gold": "#c9a84c",
                        "gold-light": "#f5e6a3",
                        "gold-dark": "#a8892b",
                    },
                    fontFamily: {
                        "display-lg": ["Playfair Display", "serif"],
                        "body-md": ["'Source Sans 3'", "sans-serif"],
                        "label-caps": ["'Source Sans 3'", "sans-serif"],
                    }
                }
            }
        }
    </script>
</head>
<body>
<header class="w-full top-0 sticky z-50 bg-white border-b border-gold/20 shadow-sm">
    <div class="flex flex-col md:flex-row justify-between items-center px-6 md:px-16 py-4 max-w-[1280px] mx-auto">
        <!-- ====== الشعار ====== -->
        <a class="heading-luxury text-3xl md:text-4xl tracking-tighter text-[#0b0c0c] hover:text-gold transition-colors" href="index.php">
            L'ESSENCE
            <span class="text-gold text-[10px] block font-body-md tracking-[0.3em] uppercase">PARFUMERIE</span>
        </a>

        <!-- ====== القوائم الرئيسية ====== -->
        <div class="hidden md:flex gap-8 items-center">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">Fragrances</a>
            <?php if ($isAdmin): ?>
                <a class="nav-link text-gold" href="admin/admin_dashboard.php">✦ Admin</a>
            <?php endif; ?>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
        </div>

        <!-- ====== البحث + الأيقونات ====== -->
        <div class="flex items-center gap-5 mt-3 md:mt-0">
            <!-- شريط البحث -->
            <form method="GET" action="products.php" class="hidden md:flex items-center">
                <input type="text" name="search" placeholder="Search fragrances..." 
                       class="border border-[#e5e2e1] rounded-l-full px-4 py-1 text-sm focus:outline-none focus:border-gold transition-colors w-40" />
                <button type="submit" class="bg-gold text-black px-4 py-1 rounded-r-full text-sm font-bold hover:bg-gold-dark transition-colors">
                    Search
                </button>
            </form>

            <!-- أيقونة البحث للشاشات الصغيرة -->
            <button class="md:hidden text-[#444748] hover:text-gold transition-colors" onclick="document.getElementById('mobile-search').classList.toggle('hidden')">
                <span class="material-symbols-outlined text-2xl">search</span>
            </button>

            <!-- السلة -->
            <a href="cart.php" class="relative text-[#444748] hover:text-gold transition-colors">
                <span class="material-symbols-outlined text-2xl">shopping_bag</span>
                <span class="cart-badge"><?php echo $cartCount; ?></span>
            </a>

            <!-- المستخدم -->
            <?php if ($isLoggedIn): ?>
                <div class="flex items-center gap-3">
                    <a href="profile.php" class="text-sm font-medium text-[#444748] hover:text-gold transition-colors">
                        👤 <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Profile'); ?>
                    </a>
                    <span class="text-[#444748]/20">|</span>
                    <a href="logout.php" class="text-sm font-medium text-[#ba1a1a]/70 hover:text-[#ba1a1a] transition-colors">Logout</a>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-3">
                    <a href="login.php" class="text-sm font-medium text-[#444748] hover:text-gold transition-colors">Login</a>
                    <span class="text-[#444748]/20">|</span>
                    <a href="register.php" class="btn-gold btn-gold-sm">Register</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ====== خط زخرفي ذهبي ====== -->
        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-gold/50 to-transparent"></div>
    </div>

    <!-- ====== بحث للشاشات الصغيرة ====== -->
    <div id="mobile-search" class="hidden md:hidden px-6 pb-4">
        <form method="GET" action="products.php" class="flex">
            <input type="text" name="search" placeholder="Search fragrances..." 
                   class="flex-1 border border-[#e5e2e1] rounded-l-full px-4 py-2 text-sm focus:outline-none focus:border-gold transition-colors" />
            <button type="submit" class="bg-gold text-black px-6 py-2 rounded-r-full text-sm font-bold hover:bg-gold-dark transition-colors">
                Search
            </button>
        </form>
    </div>
</header>