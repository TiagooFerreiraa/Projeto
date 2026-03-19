<?php
  session_start();
  include '../../Connection/connection.php';

  if (!isset($_SESSION['user'])) {
    header("Location: ../../Authentication/login.php");
    exit();
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ---- Atualizar produto ----

    $id = intval($_POST['id']);
    $name = $_POST['Name'];
    $description = $_POST['Description'];
    $price = $_POST['Price'];
    $stock = $_POST['Stock'];

    $imageDataUrl = null;
    if (isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK) {
      $imageData = file_get_contents($_FILES['Image']['tmp_name']);
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mimeType = finfo_file($finfo, $_FILES['Image']['tmp_name']);
      finfo_close($finfo);
      $imageDataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }

    if ($imageDataUrl !== null) {
      $sql = "UPDATE products SET Name = ?, Description = ?, Price = ?, Stock = ?, Image = ? WHERE ID = ?";
      $stmt = mysqli_prepare($connection, $sql);
      mysqli_stmt_bind_param($stmt, "ssdiss", $name, $description, $price, $stock, $imageDataUrl, $id);
    } else {
      $sql = "UPDATE products SET Name = ?, Description = ?, Price = ?, Stock = ? WHERE ID = ?";
      $stmt = mysqli_prepare($connection, $sql);
      mysqli_stmt_bind_param($stmt, "ssssi", $name, $description, $price, $stock, $id);
    }

    if (!mysqli_stmt_execute($stmt)) {
      die("Error updating product: " . mysqli_error($connection));
    }
    mysqli_stmt_close($stmt);

    header("Location: products_management.php");
    exit();
  }

  if (!isset($_GET['id'])) {
    header("Location: products_management.php");
    exit();
  }

  $id = intval($_GET['id']);
  $sql = "SELECT * FROM products WHERE ID = $id";
  $result = mysqli_query($connection, $sql);
  $product = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Produto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <style>
    body {
      background: url('../../Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
    }
  </style>
</head>
<body class="container mt-5">
  <h2 class="mb-4">Editar Produto</h2>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $product['ID'] ?>">

    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="Name" class="form-control" value="<?= htmlspecialchars($product['Name']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descrição</label>
      <input type="text" name="Description" class="form-control" value="<?= htmlspecialchars($product['Description']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Preço</label>
      <input type="number" name="Price" class="form-control" value="<?= htmlspecialchars($product['Price']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Estoque</label>
      <input type="number" name="Stock" class="form-control" value="<?= htmlspecialchars($product['Stock']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Imagem Atual</label><br>
      <?php
        $currentImageSrc = 'https://via.placeholder.com/120?text=No+image';
        if (!empty($product['Image'])) {
          if (strpos($product['Image'], 'data:') === 0) {
            $currentImageSrc = $product['Image'];
          } else {
            $currentImageSrc = htmlspecialchars($product['Image']);
          }
        }
      ?>
      <img src="<?= $currentImageSrc ?>" width="120" class="mb-2">
    </div>
    <div class="mb-3">
      <label class="form-label">Mudar Imagem</label>
      <input type="file" name="Image" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Atualizar Produto</button>
    <a href="products_management.php" class="btn btn-secondary">Cancelar</a>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>