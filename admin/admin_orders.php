<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$status, $order_id]);
        $success = 'Order status updated!';
    } catch (PDOException $e) {
        $error = 'Error updating order status.';
    }
}

$orders = $pdo->query("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC")->fetchAll();

$order_details = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
    $stmt->execute([$_GET['view']]);
    $order_details = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manage Orders | Admin</title>
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
                        "error": "#ba1a1a",
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
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300; }
        body { background: #fdf8f8; color: #1c1b1b; }
        .table-row:hover { background: #f7f3f2; }
        .status-badge { padding: 2px 12px; border-radius: 9999px; font-size: 11px; text-transform: uppercase; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<header class="w-full bg-primary text-white">
    <div class="flex justify-between items-center px-6 md:px-16 py-4 max-w-[1280px] mx-auto">
        <div class="flex items-center gap-8">
            <a class="font-display-lg text-2xl tracking-tighter" href="admin_dashboard.php">L'ESSENCE Admin</a>
            <nav class="hidden md:flex gap-6 text-sm uppercase tracking-widest">
                <a class="text-white/70 hover:text-white" href="admin_dashboard.php">Dashboard</a>
                <a class="text-white/70 hover:text-white" href="admin_products.php">Products</a>
                <a class="text-white font-bold" href="admin_orders.php">Orders</a>
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
    <h1 class="font-display-lg text-4xl mb-8">Manage Orders</h1>

    <?php if (isset($success)): ?>
        <div class="bg-green-50 text-green-700 p-4 mb-6 border border-green-200"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="bg-error/10 text-error p-4 mb-6 border border-error/20"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['view']) && $order_details): ?>
    <div class="bg-surface-container-low p-6 mb-8 border border-outline-variant">
        <div class="flex justify-between items-start mb-6">
            <h2 class="font-display-lg text-2xl">Order #<?php echo (int)$_GET['view']; ?></h2>
            <a href="admin_orders.php" class="text-primary hover:underline">← Back to Orders</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-outline-variant">
                    <tr class="text-left text-xs uppercase tracking-widest text-on-surface-variant">
                        <th class="py-2 pr-4">Product</th>
                        <th class="py-2 px-4">Quantity</th>
                        <th class="py-2 px-4">Unit Price</th>
                        <th class="py-2 px-4">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_details as $item): ?>
                    <tr class="border-b border-outline-variant">
                        <td class="py-3 pr-4"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td class="py-3 px-4"><?php echo $item['quantity']; ?></td>
                        <td class="py-3 px-4">$<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="py-3 px-4 font-bold">$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full border border-outline-variant">
            <thead class="bg-surface-container-low">
                <tr class="text-left text-xs uppercase tracking-widest">
                    <th class="p-4">Order ID</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr class="table-row border-t border-outline-variant">
                    <td class="p-4 font-bold">#<?php echo $order['order_id']; ?></td>
                    <td class="p-4">
                        <p class="font-bold"><?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($order['email']); ?></p>
                    </td>
                    <td class="p-4 text-sm"><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                    <td class="p-4 font-bold">$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td class="p-4">
                        <form method="POST" action="" class="flex items-center gap-2">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>" />
                            <select name="status" class="border border-outline-variant bg-white px-2 py-1 text-sm">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="text-xs bg-primary text-white px-3 py-1 hover:opacity-90">Update</button>
                        </form>
                    </td>
                    <td class="p-4 text-center">
                        <a href="?view=<?php echo $order['order_id']; ?>" class="text-primary hover:underline text-sm">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="w-full py-8 bg-surface-container-low border-t border-outline-variant mt-12">
    <div class="max-w-[1280px] mx-auto px-6 md:px-16 text-center text-sm text-on-surface-variant">
        <p>© <?php echo date('Y'); ?> L'ESSENCE Admin Panel</p>
    </div>
</footer>
</body>
</html>