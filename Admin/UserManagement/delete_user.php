<?php
  include '../../Connection/connection.php';

  if (isset($_GET['id'])) {
    // ---- Pegar ID do utilizador ----
    $id = intval($_GET['id']);

    $sql = "DELETE FROM users WHERE ID = '$id'";
    mysqli_query($connection, $sql);

    header("Location: users_management.php");
    exit();
  }
?>