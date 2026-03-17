<?php
session_start();
include '../Connection/connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../Authentication/login.php');
  exit();
}

$userId = intval($_SESSION['user_id']);
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

if ($productId <= 0) {
  header('Location: ../index.php');
  exit();
}

// Ensure user has an active cart
$cartSql = "SELECT ID FROM carts WHERE User_ID = ? AND Status = 'active' LIMIT 1";
$stmt = $connection->prepare($cartSql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$cartResult = $stmt->get_result();
$cart = $cartResult->fetch_assoc();
$stmt->close();

if (!$cart) {
  $insertSql = "INSERT INTO carts (User_ID) VALUES (?)";
  $stmt = $connection->prepare($insertSql);
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $cartId = $connection->insert_id;
  $stmt->close();
} else {
  $cartId = $cart['ID'];
}

// Add or update item
$checkSql = "SELECT ID, Quantity FROM cart_items WHERE Cart_ID = ? AND Product_ID = ?";
$stmt = $connection->prepare($checkSql);
$stmt->bind_param('ii', $cartId, $productId);
$stmt->execute();
$checkResult = $stmt->get_result();
$item = $checkResult->fetch_assoc();
$stmt->close();

if ($item) {
  $newQty = $item['Quantity'] + $quantity;
  $updateSql = "UPDATE cart_items SET Quantity = ? WHERE ID = ?";
  $stmt = $connection->prepare($updateSql);
  $stmt->bind_param('ii', $newQty, $item['ID']);
  $stmt->execute();
  $stmt->close();
} else {
  $insertSql = "INSERT INTO cart_items (Cart_ID, Product_ID, Quantity) VALUES (?, ?, ?)";
  $stmt = $connection->prepare($insertSql);
  $stmt->bind_param('iii', $cartId, $productId, $quantity);
  $stmt->execute();
  $stmt->close();
}

// After adding to cart, always go to the cart page.
header('Location: cart.php');
exit();
