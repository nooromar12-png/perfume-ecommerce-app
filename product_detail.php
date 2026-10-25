<?php
$page_title = 'Product Details';
require_once 'config/database.php';
include 'includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit;
    }

    // جلب منتجات مشابهة
    $stmt2 = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND product_id != ? LIMIT 3");
    $stmt2->execute([$product['category_id'], $product_id]);
    $related = $stmt2->fetchAll();

} catch (PDOException $e) {
    header('Location: products.php');
    exit;
}
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <div class="flex gap-2 text-sm text-on-surface-variant mb-8">
        <a href="index.php" class="hover:text-primary">Home</a>
        <span>/</span>
        <a href="products.php" class="hover:text-primary">Fragrances</a>
        <span>/</span>
        <span class="text-primary"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <div class="grid md:grid-cols-2 gap-12">
        <!-- Image -->
        <div class="bg-surface-container-low aspect-[3/4] overflow-hidden">
            <img class="w-full h-full object-cover" 
                 src="<?php echo htmlspecialchars($product['image_url'] ?: 'assets/images/placeholder.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" />
        </div>

        <!-- Info -->
        <div>
            <h1 class="font-display-lg text-4xl md:text-6xl mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-secondary text-sm uppercase tracking-widest mb-4"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></p>
            <p class="text-2xl font-bold mb-6">$<?php echo number_format($product['price'], 2); ?></p>

            <p class="text-on-surface-variant leading-relaxed mb-8"><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>

            <div class="flex items-center gap-4 mb-6">
                <label class="text-sm text-on-surface-variant">Quantity:</label>
                <div class="flex border border-outline-variant">
                    <button class="px-4 py-2 hover:bg-surface-container-low" onclick="changeQty(-1)">−</button>
                    <input id="qty-input" class="w-12 text-center bg-transparent border-none" type="text" value="1" readonly />
                    <button class="px-4 py-2 hover:bg-surface-container-low" onclick="changeQty(1)">+</button>
                </div>
                <?php if ($isLoggedIn): ?>
                    <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                            class="flex-1 bg-primary text-white py-3 px-6 uppercase text-sm tracking-widest hover:opacity-90 transition-all" 
                            id="add-to-cart-btn">
                        Add to Cart
                    </button>
                <?php else: ?>
                    <button onclick="window.location.href='login.php'" 
                            class="flex-1 border border-primary py-3 px-6 uppercase text-sm tracking-widest hover:bg-primary hover:text-white transition-all">
                        Login to Add
                    </button>
                <?php endif; ?>
            </div>

            <div class="text-sm text-on-surface-variant">
                <p><span class="font-bold text-primary">Stock:</span> <?php echo $product['stock_quantity']; ?> available</p>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <section class="mt-20">
        <h2 class="font-display-lg text-3xl mb-8">You May Also Like</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($related as $rel): ?>
            <div class="text-center cursor-pointer hover:opacity-80" onclick="window.location.href='product_detail.php?id=<?php echo $rel['product_id']; ?>'">
                <div class="bg-surface-container-low aspect-[3/4] mb-3 overflow-hidden">
                    <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($rel['image_url'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>" />
                </div>
                <h3 class="font-bold"><?php echo htmlspecialchars($rel['name']); ?></h3>
                <p class="text-sm text-secondary">$<?php echo number_format($rel['price'], 2); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
function changeQty(delta) {
    const input = document.getElementById('qty-input');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    input.value = val;
}

function addToCart(productId) {
    const qty = parseInt(document.getElementById('qty-input').value);
    <?php if ($isLoggedIn): ?>
        fetch('scripts/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productId + '&quantity=' + qty
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById('add-to-cart-btn');
                btn.textContent = '✓ Added!';
                btn.style.background = '#2d8a4e';
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.cart_count;
                });
                setTimeout(() => {
                    btn.textContent = 'Add to Cart';
                    btn.style.background = '';
                }, 1500);
            } else {
                alert(data.message || 'Error adding to cart');
            }
        });
    <?php else: ?>
        alert('Please login to add items to cart.');
        window.location.href = 'login.php';
    <?php endif; ?>
}
</script>

<?php include 'includes/footer.php'; ?>