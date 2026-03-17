<?php
session_start();
include '../Connection/connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../Authentication/login.php');
  exit();
}

$cartItemId = isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0;

if ($cartItemId > 0) {
  $sql = "DELETE FROM cart_items WHERE ID = ?";
  $stmt = $connection->prepare($sql);
  $stmt->bind_param('i', $cartItemId);
  $stmt->execute();
  $stmt->close();
}

header('Location: cart.php');
exit();
