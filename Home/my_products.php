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

// Carregar categorias para a navbar
$cat_sql = "SELECT id, name, COALESCE(Icon, 'bi-list-ul') AS Icon FROM categories ORDER BY name";
$cat_result = $connection->query($cat_sql);
$categories = [];
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);

    $stmt = $connection->prepare('SELECT ID FROM products WHERE ID = ? AND Publisher_ID = ?');
    $stmt->bind_param('ii', $deleteId, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $error = 'Produto não encontrado ou não pertence a si.';
    } else {
        $stmt->close();
        $connection->begin_transaction();

        $deleteCartStmt = $connection->prepare('DELETE FROM cart_items WHERE Product_ID = ?');
        $deleteCartStmt->bind_param('i', $deleteId);
        $deleteCartStmt->execute();
        $deleteCartStmt->close();

        $deleteStmt = $connection->prepare('DELETE FROM products WHERE ID = ?');
        $deleteStmt->bind_param('i', $deleteId);
        if ($deleteStmt->execute()) {
            $message = 'Produto removido com sucesso.';
            $connection->commit();
        } else {
            $connection->rollback();
            $error = 'Falha ao remover produto: ' . $connection->error;
        }
        $deleteStmt->close();
    }
}

$products = [];
$sql = "SELECT products.*, categories.Name AS Category_Name FROM products LEFT JOIN categories ON products.Category_ID = categories.ID WHERE products.Publisher_ID = ? ORDER BY products.ID";
$stmt = $connection->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meus Produtos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="../Images/logoo.png">
  <style>
    body {
      background: url('../Images/main_bg.png') no-repeat center center fixed;
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
      <a class="navbar-brand" href="../index.php">NovusStore</a>
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
              <a class="nav-link" href="../index.php">
                <i class="bi bi-house-door-fill me-2"></i>Inicio
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../Cart/cart.php">
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
              <a href="profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Perfil</a>
            </li>
            <li class="nav-item">
              <a href="sell_product.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i>Vender um Produto</a>
            </li>
            <li class="nav-item">
              <a href="my_products.php" class="nav-link active"><i class="bi bi-card-checklist me-2"></i>Meus Produtos</a>
            </li>
            <li class="nav-item">
              <a href="../Authentication/logout.php" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i>Terminar sessão</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1>Meus Produtos</h1>
        <p class="text-muted">Veja e gerencie os produtos que você está a vender.</p>
      </div>
      <a href="sell_product.php" class="btn btn-success">Publicar novo produto</a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($products)): ?>
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($products as $product): ?>
          <div class="col">
            <div class="card h-100">
              <?php
                $imageSrc = '../Images/default.png';
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
                <p class="card-text"><strong>Categoria:</strong> <?= htmlspecialchars($product['Category_Name'] ?? 'Sem categoria') ?></p>
                <p class="card-text"><strong>Preço:</strong> $<?= htmlspecialchars($product['Price']) ?></p>
                <p class="card-text"><strong>Estoque:</strong> <?= htmlspecialchars($product['Stock']) ?></p>
                <div class="mt-auto d-flex gap-2">
                  <a href="edit_my_product.php?id=<?= $product['ID'] ?>" class="btn btn-outline-primary btn-sm flex-fill">Editar</a>
                  <form method="POST" onsubmit="return confirm('Tem certeza que deseja apagar este produto?');" class="flex-fill">
                    <input type="hidden" name="delete_id" value="<?= $product['ID'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Apagar</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info">Ainda não tens produtos publicados.</div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
