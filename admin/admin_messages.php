<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/database.php';

if (isset($_GET['read'])) {
    $message_id = (int)$_GET['read'];
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET is_read = TRUE WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $success = 'Message marked as read.';
    } catch (PDOException $e) {
        $error = 'Error updating message.';
    }
}

if (isset($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $success = 'Message deleted.';
    } catch (PDOException $e) {
        $error = 'Error deleting message.';
    }
}

$messages = $pdo->query("SELECT * FROM contacts ORDER BY is_read ASC, submitted_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manage Messages | Admin</title>
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
        .unread { font-weight: bold; background: #fef3c7; }
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
                <a class="text-white/70 hover:text-white" href="admin_users.php">Users</a>
                <a class="text-white font-bold" href="admin_messages.php">Messages</a>
            </nav>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></span>
            <a href="../logout.php" class="text-white/70 hover:text-white">Logout</a>
        </div>
    </div>
</header>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <h1 class="font-display-lg text-4xl mb-8">Messages</h1>

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
                    <th class="p-4">From</th>
                    <th class="p-4">Subject</th>
                    <th class="p-4">Message</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr class="table-row border-t border-outline-variant <?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                    <td class="p-4">#<?php echo $msg['message_id']; ?></td>
                    <td class="p-4">
                        <p class="font-bold"><?php echo htmlspecialchars($msg['name']); ?></p>
                        <p class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($msg['email']); ?></p>
                    </td>
                    <td class="p-4"><?php echo htmlspecialchars($msg['subject'] ?? 'General'); ?></td>
                    <td class="p-4 max-w-xs truncate"><?php echo htmlspecialchars(substr($msg['message'], 0, 80)) . (strlen($msg['message']) > 80 ? '...' : ''); ?></td>
                    <td class="p-4 text-sm"><?php echo date('M j, Y g:i A', strtotime($msg['submitted_at'])); ?></td>
                    <td class="p-4">
                        <?php if ($msg['is_read']): ?>
                            <span class="text-green-600">✓ Read</span>
                        <?php else: ?>
                            <span class="text-error font-bold">● New</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if (!$msg['is_read']): ?>
                            <a href="?read=<?php echo $msg['message_id']; ?>" class="text-primary hover:underline text-sm">Mark Read</a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $msg['message_id']; ?>" class="text-error hover:underline text-sm <?php echo $msg['is_read'] ? '' : 'ml-4'; ?>" onclick="return confirm('Delete this message?')">Delete</a>
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