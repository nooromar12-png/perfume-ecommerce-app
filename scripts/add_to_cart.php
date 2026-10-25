<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    // التحقق من المخزون
    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // التحقق من الكمية في السلة
    $stmt2 = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt2->execute([$user_id, $product_id]);
    $existing = $stmt2->fetch();

    $total_qty = ($existing ? $existing['quantity'] : 0) + $quantity;

    if ($total_qty > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
        exit;
    }

    // إضافة أو تحديث
    if ($existing) {
        $stmt3 = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt3->execute([$total_qty, $user_id, $product_id]);
    } else {
        $stmt3 = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt3->execute([$user_id, $product_id, $quantity]);
    }

    // جلب عدد العناصر
    $stmt4 = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
    $stmt4->execute([$user_id]);
    $count = $stmt4->fetch();

    echo json_encode(['success' => true, 'cart_count' => $count['total'] ?? 0]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>