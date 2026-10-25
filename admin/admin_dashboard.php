<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/database.php';

try {
    $total_products = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
    $total_orders = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
    $total_users = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch()['count'];
    $unread_messages = $pdo->query("SELECT COUNT(*) as count FROM contacts WHERE is_read = FALSE")->fetch()['count'];
} catch (PDOException $e) {
    $total_products = $total_orders = $total_users = $unread_messages = 0;
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Dashboard | L'ESSENCE</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Source+Sans+3:wght@300;400;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
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
                        "surface-container-low": "#f7f3f2",
                        "secondary": "#685c52",
                        "on-primary": "#ffffff",
                    },
                    fontFamily: {
                        "display-lg": ["Playfair Display"],
                        "body-md": ["'Source Sans 3'"],
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300;
        }
        body {
            background: #fdf8f8;
            color: #1c1b1b;
        }
        .stat-card {
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
    </style>
</head>
<body>

<header class="w-full bg-primary text-white">
    <div class="flex justify-between items-center px-6 md:px-16 py-4 max-w-[1280px] mx-auto">
        <div class="flex items-center gap-8">
            <a class="font-display-lg text-2xl tracking-tighter" href="admin_dashboard.php">L'ESSENCE Admin</a>
            <nav class="hidden md:flex gap-6 text-sm uppercase tracking-widest">
                <a class="text-white font-bold" href="admin_dashboard.php">Dashboard</a>
                <a class="text-white/70 hover:text-white" href="admin_products.php">Products</a>
                <a class="text-white/70 hover:text-white" href="admin_orders.php">Orders</a>
                <a class="text-white/70 hover:text-white" href="admin_users.php">Users</a>
                <a class="text-white/70 hover:text-white" href="admin_messages.php">Messages</a>
            </nav>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></span>
            <a href="../logout.php" class="text-white/70 hover:text-white">Logout</a>
        </div>
    </div>
</header>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <h1 class="font-display-lg text-4xl md:text-5xl mb-8">Dashboard</h1>
    <p class="text-on-surface-variant mb-12">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</p>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="stat-card bg-surface-container-low p-6 text-center border border-outline-variant">
            <p class="text-3xl font-bold"><?php echo $total_products; ?></p>
            <p class="text-sm text-on-surface-variant uppercase tracking-widest">Products</p>
            <a href="admin_products.php" class="text-xs text-primary hover:underline">Manage →</a>
        </div>
        <div class="stat-card bg-surface-container-low p-6 text-center border border-outline-variant">
            <p class="text-3xl font-bold"><?php echo $total_orders; ?></p>
            <p class="text-sm text-on-surface-variant uppercase tracking-widest">Orders</p>
            <a href="admin_orders.php" class="text-xs text-primary hover:underline">Manage →</a>
        </div>
        <div class="stat-card bg-surface-container-low p-6 text-center border border-outline-variant">
            <p class="text-3xl font-bold"><?php echo $total_users; ?></p>
            <p class="text-sm text-on-surface-variant uppercase tracking-widest">Customers</p>
            <a href="admin_users.php" class="text-xs text-primary hover:underline">Manage →</a>
        </div>
        <div class="stat-card bg-surface-container-low p-6 text-center border border-outline-variant">
            <p class="text-3xl font-bold <?php echo $unread_messages > 0 ? 'text-error' : ''; ?>"><?php echo $unread_messages; ?></p>
            <p class="text-sm text-on-surface-variant uppercase tracking-widest">Unread Messages</p>
            <a href="admin_messages.php" class="text-xs text-primary hover:underline">Manage →</a>
        </div>
    </div>
</main>

<footer class="w-full py-8 bg-surface-container-low border-t border-outline-variant mt-12">
    <div class="max-w-[1280px] mx-auto px-6 md:px-16 text-center text-sm text-on-surface-variant">
        <p>© <?php echo date('Y'); ?> L'ESSENCE Admin Panel</p>
    </div>
</footer>
</body>
</html>