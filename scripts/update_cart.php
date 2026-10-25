<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$delta = isset($_POST['delta']) ? (int)$_POST['delta'] : 0;

if ($cart_id <= 0 || $delta == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("
        SELECT ci.quantity, ci.product_id, p.stock_quantity 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.product_id 
        WHERE ci.cart_id = ? AND ci.user_id = ?
    ");
    $stmt->execute([$cart_id, $user_id]);
    $item = $stmt->fetch();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    $new_qty = $item['quantity'] + $delta;

    if ($new_qty < 1) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be less than 1']);
        exit;
    }

    if ($new_qty > $item['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
        exit;
    }

    $stmt2 = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    $stmt2->execute([$new_qty, $cart_id, $user_id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>