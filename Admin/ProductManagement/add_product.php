<?php
  session_start();
  include '../../Connection/connection.php';

  // ---- Verificar se utilizador está logado ----
  if (!isset($_SESSION['user'])) {
    header("Location: ../../Authentication/login.php");
    exit();
  }

  // ---- Verificar se é admin ----
  if (!isset($_SESSION['Is_Admin']) || $_SESSION['Is_Admin'] != 1) {
    header("Location: ../../index.php");
    exit();
  }

  $cat_sql = "SELECT ID, Name FROM categories ORDER BY Name";
  $cat_result = mysqli_query($connection, $cat_sql);

  // ---- Inserir utilizador ----
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['Category_ID'];
    $name = $_POST['Name'];
    $description = $_POST['Description'];
    $price = $_POST['Price'];
    $stock = $_POST['Stock'];
    $publisher_id = $_SESSION['user_id'];

    $imageData = file_get_contents($_FILES['Image']['tmp_name']);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['Image']['tmp_name']);
    finfo_close($finfo);

    $imageDataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

    $sql = "INSERT INTO products (Category_ID, Name, Description, Price, Stock, Image, Publisher_ID) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "issdisi", $category_id, $name, $description, $price, $stock, $imageDataUrl, $publisher_id);

    if (!mysqli_stmt_execute($stmt)) {
      die("Error creating product: " . mysqli_error($connection));
    }

    mysqli_stmt_close($stmt);

    header("Location: products_management.php");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adicionar Produto</title>
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
  <h2 class="mb-4">Adicionar Produto</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Categoria</label>
      <select class="form-select" name="Category_ID">
        <?php while($cat = mysqli_fetch_assoc($cat_result)) { ?>
          <option value="<?php echo $cat['ID']; ?>">
            <?php echo htmlspecialchars($cat['Name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="Name" class="form-control" required placeholder="Ex: produto123">
    </div>
    <div class="mb-3">
      <label class="form-label">Descrição</label>
      <input type="text" name="Description" class="form-control" required placeholder="Insira uma descrição para o produto">
    </div>
    <div class="mb-3">
      <label class="form-label">Preço</label>
      <input type="number" name="Price" class="form-control" required placeholder="Ex: 2.99">
    </div>
    <div class="mb-3">
      <label class="form-label">Estoque</label>
      <input type="number" name="Stock" class="form-control" required placeholder="Ex: 20">
    </div>
    <div class="mb-3">
      <label class="form-label">Imagem do Produto</label>
      <input type="file" name="Image" class="form-control" accept="image/*" required>
    </div>
    <button type="submit" class="btn btn-primary">Criar Produto</button>
    <a href="products_management.php" class="btn btn-secondary">Cancelar</a>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>