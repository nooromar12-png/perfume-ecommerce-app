<?php
$page_title = 'Home';
require_once 'config/database.php';
include 'includes/header.php';

// جلب المنتجات المميزة
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
}
?>

<style>
    /* ====== الهيرو ====== */
    .hero-section {
        background: #fdf8f8;
        min-height: 80vh;
        display: flex;
        align-items: center;
        position: relative;
    }

    /* ====== عنصر زخرفي ذهبي ====== */
    .hero-section::before {
        content: '';
        position: absolute;
        top: -20%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(201,168,76,0.06) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -5%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(201,168,76,0.04) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .hero-title {
        font-family: 'Playfair Display', serif;
        font-size: 72px;
        font-weight: 700;
        line-height: 1.05;
        letter-spacing: -0.02em;
        color: #0b0c0c;
    }
    .hero-subtitle {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 300;
        font-style: italic;
        color: #685c52;
        letter-spacing: 0.03em;
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 40px; }
        .hero-subtitle { font-size: 16px; }
    }

    /* ====== بطاقات المنتجات ====== */
    .product-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.5s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
    }
    .product-card .product-image {
        transition: transform 0.8s ease;
    }
    .product-card:hover .product-image {
        transform: scale(1.06);
    }

    /* ====== خط فاصل ====== */
    .divider-gold {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #c9a84c, #f5e6a3, #c9a84c);
        border-radius: 50px;
        margin: 16px auto;
    }

    /* ====== قسم المنتجات ====== */
    .products-section {
        background: #f7f3ee;
        padding: 80px 0;
    }
</style>

<!-- ====== HERO ====== -->
<section class="hero-section">
    <div class="max-w-[1280px] mx-auto px-6 md:px-16 w-full relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-16">

            <!-- النص -->
            <div>
                <span class="text-gold text-xs uppercase tracking-[0.3em] border-l-4 border-gold pl-4 font-bold">
                    Since 1994
                </span>
                <h1 class="hero-title mt-6">
                    Essence of<br />
                    <span class="gold-gradient-text">Elegance</span>
                </h1>
                <p class="hero-subtitle mt-4 max-w-lg">
                    "A curated collection of olfactory masterpieces designed to evoke memory, emotion, and timeless sophistication."
                </p>
                <div class="divider-gold"></div>
                <div class="flex flex-wrap gap-4 mt-8">
                    <a class="btn-gold" href="products.php">✦ Explore Fragrances</a>
                    <a class="btn-outline-gold" href="#featured">Discover More</a>
                </div>
            </div>

            <!-- الصورة -->
            <div class="relative">
                <div class="aspect-[4/3] overflow-hidden rounded-2xl shadow-xl">
                    <img class="w-full h-full object-cover" 
                         src="images/hero-image.png" 
                         alt="L'ESSENCE Hero" />
                </div>
                <!-- إطار ذهبي زخرفي -->
                <div class="absolute -bottom-4 -right-4 w-full h-full border-2 border-gold/20 rounded-2xl -z-10"></div>
                <div class="absolute -top-4 -left-4 w-full h-full border-2 border-gold/10 rounded-2xl -z-10"></div>
            </div>

        </div>
    </div>
</section>

<!-- ====== PRODUCTS ====== -->
<section class="products-section" id="featured">
    <div class="max-w-[1280px] mx-auto px-6 md:px-16">

        <div class="text-center mb-16">
            <span class="text-gold text-xs uppercase tracking-[0.3em] font-bold">Collection</span>
            <h2 class="heading-luxury text-4xl md:text-5xl mt-2">Featured Fragrances</h2>
            <div class="divider-gold"></div>
            <p class="text-[#685c52] italic">Discover our most exquisite olfactory creations</p>
        </div>

        <?php if (empty($featured_products)): ?>
            <div class="text-center py-20 bg-white rounded-2xl shadow-sm">
                <p class="text-on-surface-variant">No products available.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php foreach ($featured_products as $product): ?>
                <div class="product-card group cursor-pointer" onclick="window.location.href='product_detail.php?id=<?php echo $product['product_id']; ?>'">
                    <div class="relative aspect-[4/5] bg-[#f7f3ee] overflow-hidden">
                        <img class="product-image w-full h-full object-cover" 
                             src="<?php echo htmlspecialchars($product['image_url'] ?: 'images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" />
                        <?php if ($product['price'] > 200): ?>
                            <span class="absolute top-4 right-4 badge-gold">✦ Premium</span>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                    <div class="p-5 text-center bg-white">
                        <h3 class="font-serif text-lg font-semibold"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gold font-bold text-xl">$<?php echo number_format($product['price'], 2); ?></p>
                        <?php if ($isLoggedIn): ?>
                            <button onclick="event.stopPropagation(); addToCart(<?php echo $product['product_id']; ?>, this)" 
                                    class="btn-gold btn-gold-sm w-full mt-3">
                                + Add to Cart
                            </button>
                        <?php else: ?>
                            <button onclick="event.stopPropagation(); window.location.href='login.php'" 
                                    class="w-full mt-3 bg-[#444748] text-white text-xs font-bold uppercase tracking-widest py-3 rounded-full hover:bg-gold hover:text-black transition-all">
                                Login to Add
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-16">
            <a href="products.php" class="btn-gold">View All Fragrances ✦</a>
        </div>

    </div>
</section>

<script>
function addToCart(productId, btn) {
    <?php if ($isLoggedIn): ?>
        fetch('scripts/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.textContent = '✓ Added!';
                btn.style.background = '#2d8a4e';
                btn.style.color = 'white';
                document.querySelectorAll('.cart-badge').forEach(el => {
                    el.textContent = data.cart_count;
                });
                setTimeout(() => {
                    btn.textContent = '+ Add to Cart';
                    btn.style.background = '';
                    btn.style.color = '';
                }, 2000);
            } else {
                alert(data.message || 'Error adding to cart');
            }
        });
    <?php else: ?>
        alert('Please login first.');
        window.location.href = 'login.php';
    <?php endif; ?>
}
</script>

<?php include 'includes/footer.php'; ?>