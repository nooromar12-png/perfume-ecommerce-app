<?php
$page_title = 'Checkout';
require_once 'config/database.php';
include 'includes/header.php';

if (!$isLoggedIn) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $stmt2 = $pdo->prepare("
        SELECT ci.*, p.name, p.price 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.product_id 
        WHERE ci.user_id = ?
    ");
    $stmt2->execute([$user_id]);
    $cart_items = $stmt2->fetchAll();

    if (empty($cart_items)) {
        header('Location: cart.php');
        exit;
    }

    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

} catch (PDOException $e) {
    header('Location: cart.php');
    exit;
}
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <h1 class="font-display-lg text-4xl md:text-6xl mb-12">Checkout</h1>

    <div class="grid md:grid-cols-2 gap-12">
        <div>
            <form method="POST" action="scripts/order_process.php">
                <h2 class="font-display-lg text-2xl mb-6 border-b border-outline-variant pb-4">Shipping Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Full Name</label>
                        <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly />
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Email</label>
                        <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly />
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Phone</label>
                        <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="text" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly />
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Shipping Address</label>
                        <textarea class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" name="shipping_address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="w-full mt-8 bg-primary text-white py-4 uppercase text-sm tracking-widest hover:opacity-90 transition-all">
                    Place Order
                </button>
            </form>
        </div>

        <div class="bg-surface-container-low p-6 h-fit sticky top-32">
            <h2 class="font-display-lg text-2xl mb-6 border-b border-outline-variant pb-4">Order Summary</h2>
            <div class="space-y-3 mb-6">
                <?php foreach ($cart_items as $item): ?>
                <div class="flex justify-between text-sm">
                    <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                    <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="border-t border-outline-variant pt-4 flex justify-between text-xl font-bold">
                <span>Total</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>