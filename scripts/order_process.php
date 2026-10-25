<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$shipping_address = trim($_POST['shipping_address'] ?? '');

if (empty($shipping_address)) {
    $_SESSION['order_error'] = 'Shipping address is required.';
    header('Location: ../checkout.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // جلب السلة
    $stmt = $pdo->prepare("
        SELECT ci.*, p.price 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.product_id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        $pdo->rollBack();
        header('Location: ../cart.php');
        exit;
    }

    // حساب المجموع
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // إنشاء الطلب
    $stmt2 = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, shipping_address, status) 
        VALUES (?, ?, ?, 'pending')
    ");
    $stmt2->execute([$user_id, $total, $shipping_address]);
    $order_id = $pdo->lastInsertId();

    // إضافة تفاصيل الطلب
    $stmt3 = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $stmt3->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        
        // تحديث المخزون
        $stmt4 = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $stmt4->execute([$item['quantity'], $item['product_id']]);
    }

    // مسح السلة
    $stmt5 = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt5->execute([$user_id]);

    $pdo->commit();

    $_SESSION['order_success'] = 'Order placed successfully! Order #' . $order_id;
    header('Location: ../profile.php');

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['order_error'] = 'Order failed. Please try again.';
    header('Location: ../checkout.php');
}
?>