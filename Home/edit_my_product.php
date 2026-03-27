<?php
session_start();
include '../Connection/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

$userId = intval($_SESSION['user_id']);
$message = '';
$error = '';

if (!isset($_GET['id'])) {
    header('Location: my_products.php');
    exit();
}

$productId = intval($_GET['id']);

$stmt = $connection->prepare('SELECT * FROM products WHERE ID = ? AND Publisher_ID = ?');
$stmt->bind_param('ii', $productId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: my_products.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['Name'] ?? '');
    $description = trim($_POST['Description'] ?? '');
    $price = floatval($_POST['Price'] ?? 0);
    $stock = intval($_POST['Stock'] ?? 0);

    if ($name === '' || $description === '') {
        $error = 'Nome e descrição são obrigatórios.';
    } elseif ($price <= 0) {
        $error = 'Preço inválido.';
    } elseif ($stock < 0) {
        $error = 'Estoque inválido.';
    } else {
        $imageDataUrl = null;
        if (isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['Image']['tmp_name']);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['Image']['tmp_name']);
            finfo_close($finfo);
            $imageDataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        if ($imageDataUrl !== null) {
            $sql = 'UPDATE products SET Name = ?, Description = ?, Price = ?, Stock = ?, Image = ? WHERE ID = ? AND Publisher_ID = ?';
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('ssdissii', $name, $description, $price, $stock, $imageDataUrl, $productId, $userId);
        } else {
            $sql = 'UPDATE products SET Name = ?, Description = ?, Price = ?, Stock = ? WHERE ID = ? AND Publisher_ID = ?';
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('ssdiii', $name, $description, $price, $stock, $productId, $userId);
        }

        if ($stmt->execute()) {
            $message = 'Produto atualizado com sucesso.';
            $stmt->close();
            $stmt = $connection->prepare('SELECT * FROM products WHERE ID = ? AND Publisher_ID = ?');
            $stmt->bind_param('ii', $productId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error = 'Falha ao atualizar produto: ' . $connection->error;
            $stmt->close();
        }
    }
}

$currentImageSrc = '../Images/default.png';
if (!empty($product['Image'])) {
    if (strpos($product['Image'], 'data:') === 0) {
        $currentImageSrc = $product['Image'];
    } else {
        $currentImageSrc = htmlspecialchars($product['Image']);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Produto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="../Images/logoo.png">
  <style>
    body {
      background: url('../Images/main_bg.png') no-repeat center center fixed;
      background-size: cover;
      padding-top: 80px;
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <h2 class="mb-4">Editar Produto</h2>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="Name" class="form-control" value="<?= htmlspecialchars($product['Name']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Descrição</label>
        <textarea name="Description" class="form-control" rows="4" required><?= htmlspecialchars($product['Description']) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Preço</label>
        <input type="number" name="Price" class="form-control" min="0.01" step="0.01" value="<?= htmlspecialchars($product['Price']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Estoque</label>
        <input type="number" name="Stock" class="form-control" min="0" step="1" value="<?= htmlspecialchars($product['Stock']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Imagem Atual</label><br>
        <img src="<?= $currentImageSrc ?>" class="img-fluid mb-3" style="max-width: 280px;" alt="Imagem do produto">
      </div>
      <div class="mb-3">
        <label class="form-label">Mudar Imagem</label>
        <input type="file" name="Image" class="form-control" accept="image/*">
      </div>
      <button type="submit" class="btn btn-primary">Atualizar Produto</button>
      <a href="my_products.php" class="btn btn-secondary">Voltar</a>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
