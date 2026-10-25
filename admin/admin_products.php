<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/database.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $image_url = trim($_POST['image_url']);
    $category_id = $_POST['category_id'] ?: null;

    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, image_url, category_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $image_url, $category_id]);
        $success = 'Product added successfully!';
    } catch (PDOException $e) {
        $error = 'Error adding product.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $image_url = trim($_POST['image_url']);
    $category_id = $_POST['category_id'] ?: null;

    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, image_url = ?, category_id = ? WHERE product_id = ?");
        $stmt->execute([$name, $description, $price, $stock, $image_url, $category_id, $product_id]);
        $success = 'Product updated successfully!';
    } catch (PDOException $e) {
        $error = 'Error updating product.';
    }
}

if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $success = 'Product deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Cannot delete product. It may be referenced in orders.';
    }
}

$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC")->fetchAll();

$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manage Products | Admin</title>
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300;
        }
        body { background: #fdf8f8; color: #1c1b1b; }
        .input-field:focus { border-color: #0b0c0c; outline: none; }
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
                <a class="text-white font-bold" href="admin_products.php">Products</a>
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
    <div class="flex justify-between items-center mb-8">
        <h1 class="font-display-lg text-4xl">Manage Products</h1>
        <a href="?form=new" class="bg-primary text-white px-6 py-2 uppercase text-sm tracking-widest hover:opacity-90 transition-all">+ Add Product</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="bg-green-50 text-green-700 p-4 mb-6 border border-green-200"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="bg-error/10 text-error p-4 mb-6 border border-error/20"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['form']) || $edit_product): ?>
    <div class="bg-surface-container-low p-6 mb-12 border border-outline-variant">
        <h2 class="font-display-lg text-2xl mb-6"><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
        <form method="POST" action="" class="grid md:grid-cols-2 gap-4">
            <?php if ($edit_product): ?>
                <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>" />
                <input type="hidden" name="edit_product" value="1" />
            <?php else: ?>
                <input type="hidden" name="add_product" value="1" />
            <?php endif; ?>
            <div>
                <label class="text-xs uppercase tracking-widest block mb-1">Product Name *</label>
                <input class="w-full border border-outline-variant bg-white px-4 py-3 input-field" type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest block mb-1">Price *</label>
                <input class="w-full border border-outline-variant bg-white px-4 py-3 input-field" type="number" step="0.01" name="price" value="<?php echo $edit_product['price'] ?? ''; ?>" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest block mb-1">Stock *</label>
                <input class="w-full border border-outline-variant bg-white px-4 py-3 input-field" type="number" name="stock" value="<?php echo $edit_product['stock_quantity'] ?? 0; ?>" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest block mb-1">Category</label>
                <select class="w-full border border-outline-variant bg-white px-4 py-3 input-field" name="category_id">
                    <option value="">Uncategorized</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($edit_product) && $edit_product['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-widest block mb-1">Image URL</label>
                <input class="w-full border border-outline-variant bg-white px-4 py-3 input-field" type="text" name="image_url" value="<?php echo htmlspecialchars($edit_product['image_url'] ?? ''); ?>" />
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-widest block mb-1">Description</label>
                <textarea class="w-full border border-outline-variant bg-white px-4 py-3 input-field" name="description" rows="4"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
            </div>
            <div class="md:col-span-2 flex gap-4">
                <button type="submit" class="bg-primary text-white px-8 py-3 uppercase text-sm tracking-widest hover:opacity-90 transition-all">
                    <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                </button>
                <a href="admin_products.php" class="border border-outline-variant px-8 py-3 uppercase text-sm tracking-widest hover:bg-surface-container-low transition-all">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full border border-outline-variant">
            <thead class="bg-surface-container-low">
                <tr class="text-left text-xs uppercase tracking-widest">
                    <th class="p-4">ID</th>
                    <th class="p-4">Image</th>
                    <th class="p-4">Name</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Stock</th>
                    <th class="p-4">Category</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr class="table-row border-t border-outline-variant">
                    <td class="p-4">#<?php echo $product['product_id']; ?></td>
                    <td class="p-4">
                        <div class="w-12 h-12 bg-surface-container-low overflow-hidden">
                            <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($product['image_url'] ?: '../assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                        </div>
                    </td>
                    <td class="p-4 font-bold"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="p-4">$<?php echo number_format($product['price'], 2); ?></td>
                    <td class="p-4"><?php echo $product['stock_quantity']; ?></td>
                    <td class="p-4"><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                    <td class="p-4 text-center">
                        <a href="?edit=<?php echo $product['product_id']; ?>" class="text-primary hover:underline text-sm">Edit</a>
                        <a href="?delete=<?php echo $product['product_id']; ?>" class="text-error hover:underline text-sm ml-4" onclick="return confirm('Delete this product?')">Delete</a>
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