<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$role, $user_id]);
        $success = 'User role updated!';
    } catch (PDOException $e) {
        $error = 'Error updating user role.';
    }
}

if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $has_orders = $stmt->fetch()['count'] > 0;

    if ($has_orders) {
        $error = 'Cannot delete user. They have existing orders.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $success = 'User deleted successfully!';
        } catch (PDOException $e) {
            $error = 'Error deleting user.';
        }
    }
}

$users = $pdo->query("SELECT u.*, COUNT(o.order_id) as order_count FROM users u LEFT JOIN orders o ON u.user_id = o.user_id GROUP BY u.user_id ORDER BY u.user_id")->fetchAll();
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manage Users | Admin</title>
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
                <a class="text-white/70 hover:text-white" href="admin_orders.php">Orders</a>
                <a class="text-white font-bold" href="admin_users.php">Users</a>
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
    <h1 class="font-display-lg text-4xl mb-8">Manage Users</h1>

    <?php if (isset($success)): ?>
        <div class="bg-green-50 text-green-700 p-4 mb-6 border border-green-200"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="bg-error/10 text-error p-4 mb-6 border border-error/20"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full border border-outline-variant">
            <thead class="bg-surface-container-low">
                <tr class="text-left text-xs uppercase tracking-widest">
                    <th class="p-4">ID</th>
                    <th class="p-4">Name</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Role</th>
                    <th class="p-4 text-center">Orders</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="table-row border-t border-outline-variant">
                    <td class="p-4">#<?php echo $user['user_id']; ?></td>
                    <td class="p-4 font-bold"><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td class="p-4"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="p-4"><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                    <td class="p-4">
                        <form method="POST" action="" class="flex items-center gap-2">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>" />
                            <select name="role" class="border border-outline-variant bg-white px-2 py-1 text-sm">
                                <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <button type="submit" name="change_role" class="text-xs bg-primary text-white px-3 py-1 hover:opacity-90">Update</button>
                        </form>
                    </td>
                    <td class="p-4 text-center"><?php echo $user['order_count']; ?></td>
                    <td class="p-4 text-center">
                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?php echo $user['user_id']; ?>" class="text-error hover:underline text-sm" onclick="return confirm('Delete this user? This will also delete their cart data.')">
                                Delete
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-on-surface-variant">(You)</span>
                        <?php endif; ?>
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