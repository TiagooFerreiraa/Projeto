<?php
  include '../../Connection/connection.php';

  if (isset($_GET['id'])) {
    // ---- Pegar ID da categoria ----
    $id = intval($_GET['id']);

    $sql = "DELETE FROM categories WHERE ID = '$id'";
    mysqli_query($connection, $sql);

    header("Location: categories_management.php");
    exit();
  }
?>