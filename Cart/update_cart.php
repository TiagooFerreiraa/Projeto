<?php
  session_start();
  include '../Connection/connection.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentication/login.php');
    exit();
  }

  $cartItemId = isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0;
  $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

  if ($cartItemId <= 0) {
    header('Location: cart.php');
    exit();
  }

  $sql = "UPDATE cart_items SET Quantity = ? WHERE ID = ?";
  $stmt = $connection->prepare($sql);
  $stmt->bind_param('ii', $quantity, $cartItemId);
  $stmt->execute();
  $stmt->close();

  header('Location: cart.php');
  exit();
?>