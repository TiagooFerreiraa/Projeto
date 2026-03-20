<?php
  session_start();
  include 'Connection/connection.php';

  // ---- Requer login ----
  if (!isset($_SESSION['user'])) {
    header("Location: Authentication/login.php");
    exit();
  }

  // ---- Carregar categorias para a navbar ----
  $cat_sql = "SELECT id, name, COALESCE(Icon, 'bi-list-ul') AS Icon FROM categories ORDER BY name";
  $cat_result = $connection->query($cat_sql);
  $categories = [];
  if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
      $categories[] = $row;
    }
  }

  $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
  $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

  $categoryName = 'Produtos';
  if ($categoryId > 0) {
    $category_sql = "SELECT Name FROM categories WHERE ID = ?";
    $cat_stmt = $connection->prepare($category_sql);
    $cat_stmt->bind_param('i', $categoryId);
    $cat_stmt->execute();
    $category_result = $cat_stmt->get_result();
    if ($category_result && $category_result->num_rows > 0) {
      $category_row = $category_result->fetch_assoc();
      $categoryName = $category_row['Name'];
    }
    $cat_stmt->close();
  }

  $product = null;
  $products = [];

  $notFound = false;
  if ($productId > 0) {
    $sql = "SELECT products.*, categories.Name AS Category_Name, users.Username AS Publisher_Name, users.Phone_Number AS Publisher_Phone 
            FROM products 
            LEFT JOIN categories ON products.Category_ID = categories.ID 
            LEFT JOIN users ON products.Publisher_ID = users.ID 
            WHERE products.ID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    if (!$product) {
      $notFound = true;
    }
  } else {
    $sql = "SELECT products.*, categories.Name AS Category_Name, users.Username AS Publisher_Name, users.Phone_Number AS Publisher_Phone 
            FROM products 
            LEFT JOIN categories ON products.Category_ID = categories.ID 
            LEFT JOIN users ON products.Publisher_ID = users.ID";

    if ($categoryId > 0) {
      $sql .= " WHERE products.Category_ID = ?";
      $stmt = $connection->prepare($sql);
      $stmt->bind_param('i', $categoryId);
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
    } else {
      $result = $connection->query($sql);
    }

    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $products[] = $row;
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $product ? htmlspecialchars($product['Name']) : htmlspecialchars($categoryName) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="Images/logoo.png">
  <style>
    body {
      background: url('Images/main_bg.png') no-repeat center center fixed;
      background-size: cover;
      padding-top: 80px;
    }

    .card {
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .card-body {
      flex: 1 1 auto;
    }

    .card-img-top {
      height: 220px;
      object-fit: cover;
    }
  </style>
</head>
<body>
  <nav class="navbar bg-body-tertiary fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">NovusStore</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">NovusStore - Bem-vindo <?= htmlspecialchars($_SESSION['user']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
            <li class="nav-item">
              <a class="nav-link" href="index.php">
                <i class="bi bi-house-door-fill me-2"></i>Inicio
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Cart/cart.php">
                <i class="bi bi-cart-fill me-2"></i>Carrinho
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-list-ul me-2"></i>Categorias
              </a>
              <ul class="dropdown-menu">
                <?php foreach ($categories as $cat): ?>
                  <li>
                    <a class="dropdown-item" href="products.php?category_id=<?= $cat['id'] ?>">
                      <i class="bi <?= htmlspecialchars($cat['Icon'] ?? 'bi-list-ul') ?> me-2"></i>
                      <?= htmlspecialchars($cat['name']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </li>
            <li class="nav-item">
              <a href="profile.php" class="nav-link">
                <i class="bi bi-person-circle me-2"></i>Perfil
              </a>
            </li>
            <li class="nav-item">
              <a href="Authentication/logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right me-2"></i>Terminar sessão
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <main class="container my-5">
    <?php if ($notFound): ?>
      <div class="alert alert-warning" role="alert">
        Sem Produtos Encontrados
      </div>
    <?php elseif ($product): ?>
      <div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 220px);">
        <div class="col-md-8">
          <div class="card">
            <?php
              $imageSrc = 'Images/default.png';
              if (!empty($product['Image'])) {
                if (strpos($product['Image'], 'data:') === 0) {
                  $imageSrc = $product['Image'];
                } else {
                  $imageSrc = htmlspecialchars($product['Image']);
                }
              }
            ?>
            <img src="<?= $imageSrc ?>" class="card-img-top" alt="<?= htmlspecialchars($product['Name']) ?>">
            <div class="card-body">
              <h1 class="card-title"><?= htmlspecialchars($product['Name']) ?></h1>
              <p class="card-text"><?= nl2br(htmlspecialchars($product['Description'])) ?></p>
              <p class="card-text"><strong>Categoria:</strong> <?= htmlspecialchars($product['Category_Name']) ?></p>
              <p class="card-text"><strong>Vendedor:</strong> <?= htmlspecialchars($product['Publisher_Name'] ?? 'Desconhecido') ?> <br><small><strong>Tel:</strong> <?= htmlspecialchars($product['Publisher_Phone'] ?? 'N/D') ?></small></p>
              <p class="card-text"><strong>Preço:</strong> $<?= htmlspecialchars($product['Price']) ?></p>
              <p class="card-text"><strong>Unidades:</strong> <?= htmlspecialchars($product['Stock']) ?></p>
              <form method="POST" action="Cart/add_to_cart.php" class="d-flex gap-2">
                <input type="hidden" name="product_id" value="<?= $product['ID'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= htmlspecialchars($product['Stock']) ?>" class="form-control" style="width: 90px;">
                <button type="submit" class="btn btn-success">Adicionar ao Carrinho</button>
              </form>
              <a href="index.php" class="btn btn-secondary mt-2">Voltar</a>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <h1><?= htmlspecialchars($categoryName) ?></h1>
      <?php if (!empty($products)): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
          <?php foreach ($products as $product): ?>
            <div class="col">
              <div class="card h-100">
                <?php
                  $imageSrc = 'Images/default.png';
                  if (!empty($product['Image'])) {
                    if (strpos($product['Image'], 'data:') === 0) {
                      $imageSrc = $product['Image'];
                    } else {
                      $imageSrc = htmlspecialchars($product['Image']);
                    }
                  }
                ?>
                <img src="<?= $imageSrc ?>" class="card-img-top" alt="<?= htmlspecialchars($product['Name']) ?>">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?= htmlspecialchars($product['Name']) ?></h5>
                  <p class="card-text"><?= htmlspecialchars($product['Description']) ?></p>
                  <p class="card-text"><strong>Vendedor:</strong> <?= htmlspecialchars($product['Publisher_Name'] ?? 'Desconhecido') ?> <br><small><strong>Tel:</strong> <?= htmlspecialchars($product['Publisher_Phone'] ?? 'N/D') ?></small></p>
                  <p class="card-text"><strong>Preço:</strong> $<?= htmlspecialchars($product['Price']) ?></p>
                  <a href="products.php?id=<?= $product['ID'] ?>" class="btn btn-primary mt-auto">Ver Produto</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>Sem produtos encontrados.</p>
      <?php endif; ?>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
