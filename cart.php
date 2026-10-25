<?php
$page_title = 'Shopping Bag';
require_once 'config/database.php';

// ====== التحقق من تسجيل الدخول ======
// يجب أن يكون قبل include 'header.php'
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

// ====== الآن نستدعي الـ Header ======
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total = 0;

try {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, p.image_url, p.stock_quantity 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.product_id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    $cart_items = [];
}
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <h1 class="heading-luxury text-4xl md:text-6xl mb-12">Shopping Bag</h1>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-16">
            <span class="material-symbols-outlined text-6xl text-on-surface-variant opacity-30">shopping_bag</span>
            <p class="mt-4 text-on-surface-variant">Your shopping bag is empty.</p>
            <a href="products.php" class="btn-gold mt-6 inline-block">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <div class="lg:col-span-2">
                <?php foreach ($cart_items as $item): ?>
                <div class="flex flex-col md:flex-row gap-6 py-6 border-b border-outline-variant" id="cart-item-<?php echo $item['cart_id']; ?>">
                    <div class="w-full md:w-32 aspect-[3/4] bg-surface-container-low overflow-hidden flex-shrink-0 rounded-lg">
                        <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($item['image_url'] ?: 'images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                    </div>
                    <div class="flex-1 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-xl"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-sm text-secondary">$<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <span class="font-bold text-gold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center gap-2">
                                <div class="flex border border-outline-variant rounded-lg">
                                    <button class="px-3 py-1 hover:bg-surface-container-low" onclick="updateCart(<?php echo $item['cart_id']; ?>, -1)">−</button>
                                    <span class="px-4 py-1" id="qty-<?php echo $item['cart_id']; ?>"><?php echo $item['quantity']; ?></span>
                                    <button class="px-3 py-1 hover:bg-surface-container-low" onclick="updateCart(<?php echo $item['cart_id']; ?>, 1)">+</button>
                                </div>
                                <span class="text-xs text-on-surface-variant">Max: <?php echo $item['stock_quantity']; ?></span>
                            </div>
                            <button class="text-sm text-on-surface-variant hover:text-error transition-colors" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">Remove</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gold/10 sticky top-32">
                    <h2 class="heading-luxury text-2xl mb-6 border-b border-outline-variant pb-4">Order Summary</h2>
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Subtotal</span>
                            <span id="subtotal" class="font-bold">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Shipping</span>
                            <span class="text-secondary">Free</span>
                        </div>
                        <div class="border-t border-outline-variant pt-4 flex justify-between text-xl font-bold">
                            <span>Total</span>
                            <span id="total" class="text-gold">$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    <a href="checkout.php" class="btn-gold btn-gold-block">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
function updateCart(cartId, delta) {
    fetch('scripts/update_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'cart_id=' + cartId + '&delta=' + delta
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating cart');
        }
    });
}

function removeFromCart(cartId) {
    if (confirm('Remove this item from your cart?')) {
        fetch('scripts/remove_from_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'cart_id=' + cartId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error removing item');
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>