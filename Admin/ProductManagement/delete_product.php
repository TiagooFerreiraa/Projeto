<?php
  include '../../Connection/Connection.php';

  if (isset($_GET['id'])) {
    // ---- Pegar ID do produto ----
    $id = intval($_GET['id']);

    $sql = "DELETE FROM products WHERE ID = '$id'";
    mysqli_query($connection, $sql);

    header("Location: products_management.php");
  }
?>