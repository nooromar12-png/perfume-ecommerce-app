<?php
$page_title = 'Profile';
require_once 'config/database.php';
include 'includes/header.php';

if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $stmt2 = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt2->execute([$user_id]);
    $orders = $stmt2->fetchAll();

} catch (PDOException $e) {
    $error = 'Error loading profile data.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name)) {
        $error = 'Full name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $phone, $address, $user_id]);
            $_SESSION['full_name'] = $full_name;
            $success = 'Profile updated successfully!';
            $stmt2 = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt2->execute([$user_id]);
            $user = $stmt2->fetch();
        } catch (PDOException $e) {
            $error = 'Error updating profile.';
        }
    }
}

if (isset($_SESSION['order_success'])) {
    $success = $_SESSION['order_success'];
    unset($_SESSION['order_success']);
}
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <h1 class="font-display-lg text-4xl md:text-6xl mb-12">My Profile</h1>

    <?php if ($success): ?>
        <div class="bg-green-50 text-green-700 p-4 mb-6 border border-green-200">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-error/10 text-error p-4 mb-6 border border-error/20">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-12">
        <!-- Profile Form -->
        <div>
            <h2 class="font-display-lg text-2xl mb-6 border-b border-outline-variant pb-4">Edit Profile</h2>
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Full Name *</label>
                    <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Email (read-only)</label>
                    <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly disabled />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Phone</label>
                    <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Address</label>
                    <textarea class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="w-full bg-primary text-white py-3 uppercase text-sm tracking-widest hover:opacity-90 transition-all">
                    Update Profile
                </button>
            </form>
            <a href="logout.php" class="block mt-4 text-center text-sm text-error border border-error px-4 py-2 hover:bg-error hover:text-white transition-all">
                Logout
            </a>
        </div>

        <!-- Order History -->
        <div>
            <h2 class="font-display-lg text-2xl mb-6 border-b border-outline-variant pb-4">Order History</h2>
            <?php if (empty($orders)): ?>
                <p class="text-on-surface-variant">No orders yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="border border-outline-variant p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold">Order #<?php echo $order['order_id']; ?></p>
                                    <p class="text-sm text-on-surface-variant"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                    <p class="text-xs uppercase tracking-widest <?php echo $order['status'] === 'delivered' ? 'text-green-600' : 'text-secondary'; ?>">
                                        <?php echo $order['status']; ?>
                                    </p>
                                </div>
                            </div>
                            <?php
                            $stmt3 = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                            $stmt3->execute([$order['order_id']]);
                            $items = $stmt3->fetchAll();
                            ?>
                            <div class="mt-2 text-sm text-on-surface-variant">
                                <?php foreach ($items as $item): ?>
                                    <div class="flex justify-between">
                                        <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                                        <span>$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>