<?php
  session_start();
  include '../../Connection/connection.php';

  if (!isset($_SESSION['user'])) {
    header('Location: ../../Authentication/login.php');
    exit();
  }

  // permitir apenas admin nesta rota
  if (!isset($_SESSION['Is_Admin']) || $_SESSION['Is_Admin'] != 1) {
    header('Location: ../../index.php');
    exit();
  }

  if (isset($_GET['id'])) {
    // ---- Pegar ID do produto ----
    $id = intval($_GET['id']);

    $connection->begin_transaction();

    // apagar eventual cart_items referenciando o produto, para evitar FK error
    $stmt = $connection->prepare('DELETE FROM cart_items WHERE Product_ID = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $connection->prepare('DELETE FROM products WHERE ID = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $connection->commit();

    header('Location: products_management.php');
    exit();
  }
?>