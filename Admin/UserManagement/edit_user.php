<?php
  session_start();
  include '../../Connection/connection.php';

  if (!isset($_SESSION['user'])) {
    header("Location: ../../Authentication/login.php");
    exit();
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ---- Atualizar utilizador ----

    $id = intval($_POST['id']);
    $username = $_POST['Username'];
    $email = $_POST['Email'];
    $phone_number = $_POST['Phone_Number'] ?? null;
    $is_admin = isset($_POST['Is_Admin']) ? 1 : 0;

    $sql = "UPDATE users SET Username = ?, Email = ?, Phone_Number = ?, Is_Admin = ? WHERE ID = ?";

    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "sssii", $username, $email, $phone_number, $is_admin, $id);
    
    if (!mysqli_stmt_execute($stmt)) {
      die("Error updating user: " . mysqli_error($connection));
    }
    mysqli_stmt_close($stmt);

    header("Location: users_management.php");
    exit();
  }

  if (!isset($_GET['id'])) {
    header("Location: users_management.php");
    exit();
  }

  $id = intval($_GET['id']);
  $sql = "SELECT * FROM users WHERE ID = $id";
  $result = mysqli_query($connection, $sql);
  $user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Utilizador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="icon" type="image/x-icon" href="../../Images/logoo.png">
  <style>
    body {
      background: url('../../Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
    }
  </style>
</head>
<body class="container mt-5">
  <h2 class="mb-4">Editar Utilizador</h2>
  <form method="POST">
    <input type="hidden" name="id" value="<?= $user['ID'] ?>">

    <div class="mb-3">
      <label class="form-label">Nome de Utilizador</label>
      <input type="text" name="Username" class="form-control" value="<?= htmlspecialchars($user['Username']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Endereço de Email</label>
      <input type="email" name="Email" class="form-control" value="<?= htmlspecialchars($user['Email']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Nº de Telemóvel</label>
      <input type="text" name="Phone_Number" class="form-control" value="<?= htmlspecialchars($user['Phone_Number'] ?? '') ?>" maxlength="9" pattern="9[0-9]{8}">
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="Is_Admin" <?= $user['Is_Admin'] ? 'checked' : '' ?>>
      <label class="form-check-label">Administrador</label>
    </div>
    <button type="submit" class="btn btn-primary">Atualizar Utilizador</button>
    <a href="users_management.php" class="btn btn-secondary">Cancelar</a>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>