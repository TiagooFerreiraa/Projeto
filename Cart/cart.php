<?php
session_start();
include '../Connection/connection.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../Authentication/login.php');
  exit();
}

$userId = intval($_SESSION['user_id']);

// Obter carrinho activo (criar se não existir)
$cartSql = "SELECT ID FROM carts WHERE User_ID = ? AND Status = 'active' LIMIT 1";
$stmt = $connection->prepare($cartSql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$cartResult = $stmt->get_result();
$cart = $cartResult->fetch_assoc();
$stmt->close();

if (!$cart) {
  $insertSql = "INSERT INTO carts (User_ID) VALUES (?)";
  $stmt = $connection->prepare($insertSql);
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $cartId = $connection->insert_id;
  $stmt->close();
} else {
  $cartId = $cart['ID'];
}

// Processar pedido de checkout (MBWay simulado)
$checkoutMessage = '';
$checkoutError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
  $mbwayPhone = trim($_POST['mbway_phone'] ?? '');

  // Validação básica: números MBWay em Portugal têm normalmente 9 dígitos
  if (!preg_match('/^\s*\d{9}\s*$/', $mbwayPhone)) {
    $checkoutError = 'Por favor introduza um número de 9 dígitos para MBWay.';
  } else {
    $deleteSql = "DELETE FROM cart_items WHERE Cart_ID = ?";
    $stmt = $connection->prepare($deleteSql);
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $stmt->close();

    $checkoutMessage = 'Payment simulated via MBWay (+' . htmlspecialchars($mbwayPhone) . '). Your cart has been cleared.';
  }
}

// Obter itens do carrinho
$sql = "SELECT ci.ID AS CartItemID, ci.Quantity, p.ID AS ProductID, p.Name, p.Price, p.Stock, p.Image
        FROM cart_items ci
        JOIN products p ON p.ID = ci.Product_ID
        WHERE ci.Cart_ID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param('i', $cartId);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
  $items[] = $row;
}
$stmt->close();

function getImageSrc($product) {
  if (!empty($product['Image']) && strpos($product['Image'], 'data:') === 0) {
    return $product['Image'];
  }
  if (!empty($product['Image'])) {
    return htmlspecialchars($product['Image']);
  }
  return 'Images/default.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url('../Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
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
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">NovusStore - Welcome <?= htmlspecialchars($_SESSION['user']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
            <li class="nav-item">
              <a class="nav-link" href="../index.php"><i class="bi bi-house-door-fill me-2"></i>Home</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-list-ul me-2"></i>Categories
              </a>
              <ul class="dropdown-menu">
                <?php
                $catSql = "SELECT id, name FROM categories ORDER BY name";
                $catResult = $connection->query($catSql);
                while ($cat = $catResult->fetch_assoc()):
                ?>
                  <li>
                    <a class="dropdown-item" href="../products.php?category_id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                  </li>
                <?php endwhile; ?>
              </ul>
            </li>
            <li class="nav-item">
              <a href="../Authentication/logout.php" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <main class="container my-5" style="padding-top: 70px;">
    <h1 class="mb-4">Your Cart</h1>

    <?php if (empty($items)): ?>
      <div class="alert alert-info">Your cart is empty. Add some products from the catalog.</div>
      <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $grandTotal = 0;
            foreach ($items as $item):
              $lineTotal = $item['Price'] * $item['Quantity'];
              $grandTotal += $lineTotal;
            ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="<?= getImageSrc($item) ?>" width="64" class="me-3" alt="<?= htmlspecialchars($item['Name']) ?>">
                    <div>
                      <div><?= htmlspecialchars($item['Name']) ?></div>
                      <div class="text-muted">Stock: <?= htmlspecialchars($item['Stock']) ?></div>
                    </div>
                  </div>
                </td>
                <td>$<?= number_format($item['Price'], 2) ?></td>
                <td>
                  <form method="POST" action="update_cart.php" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="cart_item_id" value="<?= $item['CartItemID'] ?>">
                    <input type="number" name="quantity" value="<?= $item['Quantity'] ?>" min="1" max="<?= htmlspecialchars($item['Stock']) ?>" class="form-control" style="width: 90px;">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Update</button>
                  </form>
                </td>
                <td>$<?= number_format($lineTotal, 2) ?></td>
                <td>
                  <form method="POST" action="remove_from_cart.php">
                    <input type="hidden" name="cart_item_id" value="<?= $item['CartItemID'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">Grand Total</th>
              <th>$<?= number_format($grandTotal, 2) ?></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="d-flex flex-column flex-md-row gap-2 mt-4">
        <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
        <div class="card flex-fill">
          <div class="card-body">
            <h5 class="card-title">Checkout with MBWay</h5>
            <p class="card-text text-muted mb-3">Enter a phone number to simulate payment and clear your cart.</p>
            <form method="POST" class="row g-2 align-items-end">
              <div class="col">
                <label class="form-label" for="mbway_phone">Phone (9 digits)</label>
                <input id="mbway_phone" name="mbway_phone" type="text" class="form-control" placeholder="912345678" required pattern="\d{9}">
              </div>
              <div class="col-auto">
                <button type="submit" name="checkout" class="btn btn-success w-100">Pay with MBWay</button>
              </div>
            </form>
            <p class="text-muted small mt-2 mb-0">This is a simulation. No real payment will be processed.</p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
