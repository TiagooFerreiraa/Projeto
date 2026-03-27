<?php
  session_start();
  // ---- Termina a sessão ----
  session_destroy();
  // ---- Redireciona para o login ----
  header("Location: login.php");
  exit();
?>