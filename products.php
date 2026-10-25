<?php
$page_title = 'Fragrances';
require_once 'config/database.php';
include 'includes/header.php';

// جلب التصنيفات
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// بناء استعلام المنتجات
$where = [];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where[] = "p.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "p.name LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
}

$orderBy = "p.created_at DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc': $orderBy = "p.price ASC"; break;
        case 'price_desc': $orderBy = "p.price DESC"; break;
        case 'name': $orderBy = "p.name ASC"; break;
    }
}

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY " . $orderBy;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <h1 class="font-display-lg text-4xl md:text-6xl mb-8 text-center">The Fragrance Collection</h1>
    <p class="text-center text-on-surface-variant italic mb-12">A curated anthology of olfactory narratives.</p>

    <!-- Filters -->
    <div class="flex flex-wrap justify-between items-center gap-4 mb-12 border-y border-outline-variant py-6">
        <div class="flex flex-wrap gap-4">
            <form method="GET" action="" class="flex items-center gap-2">
                <label class="text-xs uppercase tracking-widest text-on-surface-variant">Category:</label>
                <select name="category" onchange="this.form.submit()" class="border border-outline-variant bg-transparent px-3 py-1 text-sm">
                    <option value="">All</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div>
            <form method="GET" action="" class="flex items-center gap-2">
                <?php if (isset($_GET['category'])): ?>
                    <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>" />
                <?php endif; ?>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant">Sort:</label>
                <select name="sort" onchange="this.form.submit()" class="border border-outline-variant bg-transparent px-3 py-1 text-sm">
                    <option value="">Default</option>
                    <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price: Low → High</option>
                    <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price: High → Low</option>
                    <option value="name" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name') ? 'selected' : ''; ?>>Name A → Z</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <p class="text-center text-on-surface-variant py-20">No products found.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $product): ?>
            <div class="group flex flex-col items-center text-center cursor-pointer" onclick="window.location.href='product_detail.php?id=<?php echo $product['product_id']; ?>'">
                <div class="relative w-full aspect-[3/4] bg-surface-container-low overflow-hidden">
                    <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                         src="<?php echo htmlspecialchars($product['image_url'] ?: 'assets/images/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" />
                    <?php if ($isLoggedIn): ?>
                        <button onclick="event.stopPropagation(); addToCart(<?php echo $product['product_id']; ?>, this)" 
                                class="absolute bottom-0 left-0 w-full py-3 bg-primary text-white text-sm uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">
                            Quick Shop
                        </button>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-xl mt-4"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="text-xs text-secondary uppercase tracking-widest"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></p>
                <span class="mt-2">$<?php echo number_format($product['price'], 2); ?></span>
                <span class="text-xs text-on-surface-variant">Stock: <?php echo $product['stock_quantity']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

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
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.cart_count;
                });
                setTimeout(() => {
                    btn.textContent = 'Quick Shop';
                    btn.style.background = '';
                    btn.style.color = '';
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