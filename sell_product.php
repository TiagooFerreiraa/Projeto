<?php
session_start();
include 'Connection/connection.php';

// ---- Requer login ----
if (!isset($_SESSION['user'])) {
    header("Location: Authentication/login.php");
    exit();
}

$cat_sql = "SELECT ID, Name FROM categories ORDER BY Name";
$cat_result = $connection->query($cat_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = intval($_POST['Category_ID']);
    $name = trim($_POST['Name']);
    $description = trim($_POST['Description']);
    $price = floatval($_POST['Price']);
    $stock = intval($_POST['Stock']);
    $publisher_id = intval($_SESSION['user_id']);

    if (!isset($_FILES['Image']) || $_FILES['Image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Por favor, carregue uma imagem de produto.';
    } else {
        $imageData = file_get_contents($_FILES['Image']['tmp_name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['Image']['tmp_name']);
        finfo_close($finfo);

        $imageDataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

        $sql = "INSERT INTO products (Category_ID, Publisher_ID, Name, Description, Price, Stock, Image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "iissdis", $category_id, $publisher_id, $name, $description, $price, $stock, $imageDataUrl);

        if (!mysqli_stmt_execute($stmt)) {
            $error = "Erro ao criar produto: " . mysqli_error($connection);
        } else {
            mysqli_stmt_close($stmt);
            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vender Produto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <style>
    body { background: url('Images/main_bg.png') no-repeat center center fixed; background-size: cover; }
  </style>
</head>
<body class="container mt-5">
  <h2 class="mb-4">Vender um Produto</h2>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Categoria</label>
      <select class="form-select" name="Category_ID" required>
        <?php while ($cat = $cat_result->fetch_assoc()): ?>
          <option value="<?= $cat['ID'] ?>"><?= htmlspecialchars($cat['Name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="Name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Descrição</label>
      <textarea name="Description" class="form-control" rows="3" required></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Preço</label>
      <input type="number" name="Price" class="form-control" min="0.01" step="0.01" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Estoque</label>
      <input type="number" name="Stock" class="form-control" min="0" step="1" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Imagem</label>
      <input type="file" name="Image" class="form-control" accept="image/*" required>
    </div>

    <button type="submit" class="btn btn-success">Publicar Produto</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
  </form>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
