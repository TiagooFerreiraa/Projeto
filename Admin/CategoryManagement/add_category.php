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
    $name = $_POST['Name'];
    $description = $_POST['Description'];

    $sql = "INSERT INTO categories (Name, Description) VALUES (?, ?)";
    
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $name, $description);

    if (!mysqli_stmt_execute($stmt)) {
      die("Error creating user: " . mysqli_error($connection));
    }

    mysqli_stmt_close($stmt);

    header("Location: categories_management.php");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adicionar Categoria</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <style>
    body {
      background: url('../../Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
    }
  </style>
</head>
<body class="container mt-5">
  <h2 class="mb-4">Adicionar Categoria</h2>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="Name" class="form-control" required placeholder="Ex: categoria123">
    </div>
    <div class="mb-3">
      <label class="form-label">Descrição</label>
      <input type="text" name="Description" class="form-control" required placeholder="Insira uma descrição para a categoria">
    </div>
    <button type="submit" class="btn btn-primary">Criar Categoria</button>
    <a href="categories_management.php" class="btn btn-secondary">Cancelar</a>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>