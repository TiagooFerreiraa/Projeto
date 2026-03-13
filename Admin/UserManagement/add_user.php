<?php
  session_start();
  include '../../Connection/connection.php';

  // ---- Verificar se utilizador está logado ----
  if (!isset($_SESSION['user'])) {
    header("Location: ../../Authentication/login.php");
    exit();
  }

  // ---- Inserir utilizador ----
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['Email'];
    $username = $_POST['Username'];
    $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
    $is_admin = isset($_POST['Is_Admin']) ? 1 : 0;

    $sql = "INSERT INTO users (Email, Username, Password, Is_Admin) VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $email, $username, $password, $is_admin);

    if (!mysqli_stmt_execute($stmt)) {
      die("Error creating user: " . mysqli_error($connection));
    }

    mysqli_stmt_close($stmt);

    header("Location: users_management.php");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <style>
    body {
      background: url('../../Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
    }
  </style>
</head>
<body class="container mt-5">
  <h2 class="mb-4">Add User</h2>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="Email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="Username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="Password" class="form-control" required>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="Is_Admin">
      <label class="form-check-label">Admin</label>
    </div>
    <button type="submit" class="btn btn-primary">Create User</button>
    <a href="users_management.php" class="btn btn-secondary">Cancel</a>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>