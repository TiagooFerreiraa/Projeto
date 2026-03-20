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
    // Recolher item do carrinho com informação de vendedor e stock
    $itemsSql = "SELECT ci.Quantity, p.ID AS ProductID, p.Price, p.Publisher_ID, p.Stock 
                 FROM cart_items ci 
                 JOIN products p ON p.ID = ci.Product_ID 
                 WHERE ci.Cart_ID = ?";
    $stmt = $connection->prepare($itemsSql);
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $result = $stmt->get_result();

    $subtotal = 0;
    $sellerAmounts = [];
    $cartItems = [];
    $stockError = false;

    $selfPurchaseError = false;
    while ($row = $result->fetch_assoc()) {
      $cartItems[] = $row;

      if (intval($row['Publisher_ID']) === $userId) {
        $selfPurchaseError = true;
        break;
      }

      if ($row['Quantity'] > $row['Stock']) {
        $stockError = true;
        break;
      }

      $lineTotal = $row['Price'] * $row['Quantity'];
      $subtotal += $lineTotal;
      $sellerId = intval($row['Publisher_ID']);
      if ($sellerId > 0) {
        if (!isset($sellerAmounts[$sellerId])) {
          $sellerAmounts[$sellerId] = 0;
        }
        $sellerAmounts[$sellerId] += $lineTotal;
      }
    }
    $stmt->close();

    if ($selfPurchaseError) {
      $checkoutError = 'Não pode comprar os seus próprios produtos.';
    } elseif ($stockError) {
      $checkoutError = 'Erro no checkout: quantidade no carrinho excede stock disponível. Atualize o carrinho e tente novamente.';
    } else {
      $commissionRate = 0.02; // 2% de comissão do site (use 0.04 para 4%)
      $commissionValue = round($subtotal * $commissionRate, 2);
      $totalWithCommission = round($subtotal + $commissionValue, 2);

      $connection->begin_transaction();
      $canCommit = true;

      // Atualizar stock dos produtos e apagar quando ficar 0
      foreach ($cartItems as $item) {
        $newStock = intval($item['Stock']) - intval($item['Quantity']);

        if ($newStock > 0) {
          $updateStockSql = "UPDATE products SET Stock = ? WHERE ID = ?";
          $stmt = $connection->prepare($updateStockSql);
          $stmt->bind_param('ii', $newStock, $item['ProductID']);
          if (!$stmt->execute()) {
            $canCommit = false;
            break;
          }
          $stmt->close();
        } else {
          // Remover quaisquer cart_items associados antes de deletar o produto para evitar erro FK.
          $deleteCartItemsForProduct = "DELETE FROM cart_items WHERE Product_ID = ?";
          $stmt = $connection->prepare($deleteCartItemsForProduct);
          $stmt->bind_param('i', $item['ProductID']);
          if (!$stmt->execute()) {
            $canCommit = false;
            break;
          }
          $stmt->close();

          $deleteProductSql = "DELETE FROM products WHERE ID = ?";
          $stmt = $connection->prepare($deleteProductSql);
          $stmt->bind_param('i', $item['ProductID']);
          if (!$stmt->execute()) {
            $canCommit = false;
            break;
          }
          $stmt->close();
        }
      }

      // Atualizar balances dos sellers
      if ($canCommit) {
        foreach ($sellerAmounts as $sellerId => $sellerGross) {
          $sellerNet = round($sellerGross * (1 - $commissionRate), 2);
          $updateSeller = "UPDATE users SET Balance = Balance + ? WHERE ID = ?";
          $stmt = $connection->prepare($updateSeller);
          $stmt->bind_param('di', $sellerNet, $sellerId);
          if (!$stmt->execute()) {
            $canCommit = false;
            break;
          }
          $stmt->close();
        }
      }

      if ($canCommit) {
        // Esvaziar o carrinho
        $deleteSql = "DELETE FROM cart_items WHERE Cart_ID = ?";
        $stmt = $connection->prepare($deleteSql);
        $stmt->bind_param('i', $cartId);
        if (!$stmt->execute()) {
          $canCommit = false;
        }
        $stmt->close();
      }

      if ($canCommit) {
        $connection->commit();
        $checkoutMessage = sprintf(
          'Pagamento simulado via MBWay (+%s). Total do carrinho: €%.2f, Comissão: €%.2f, Total pago: €%.2f. O valor líquido foi creditado nos vendedores.',
          htmlspecialchars($mbwayPhone),
          $subtotal,
          $commissionValue,
          $totalWithCommission
        );
      } else {
        $connection->rollback();
        $checkoutError = 'Ocorreu um erro durante o processamento do checkout. Por favor tente novamente.';
      }
    }
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
  <title>Carrinho</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="../Images/logoo.png">
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
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">NovusStore - Bem-vindo <?= htmlspecialchars($_SESSION['user']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
            <li class="nav-item">
              <a class="nav-link" href="../index.php"><i class="bi bi-house-door-fill me-2"></i>Inicio</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-list-ul me-2"></i>Categorias
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
              <a href="../profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Perfil</a>
            </li>
            <li class="nav-item">
              <a href="../Authentication/logout.php" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i>Terminar sessão</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <main class="container my-5" style="padding-top: 70px;">
    <h1 class="mb-4">Carrinho</h1>

    <?php if (empty($items)): ?>
      <div class="alert alert-info">O seu carrinho está vazio. Adicione produtos pelo catálogo.</div>
      <a href="../index.php" class="btn btn-primary">Continuar comprando</a>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Produto</th>
              <th>Preço</th>
              <th>Quantidade</th>
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
                      <div class="text-muted">Unidades: <?= htmlspecialchars($item['Stock']) ?></div>
                    </div>
                  </div>
                </td>
                <td>$<?= number_format($item['Price'], 2) ?></td>
                <td>
                  <form method="POST" action="update_cart.php" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="cart_item_id" value="<?= $item['CartItemID'] ?>">
                    <input type="number" name="quantity" value="<?= $item['Quantity'] ?>" min="1" max="<?= htmlspecialchars($item['Stock']) ?>" class="form-control" style="width: 90px;">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Atualizar</button>
                  </form>
                </td>
                <td>$<?= number_format($lineTotal, 2) ?></td>
                <td>
                  <form method="POST" action="remove_from_cart.php">
                    <input type="hidden" name="cart_item_id" value="<?= $item['CartItemID'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <?php
              $commissionRate = 0.02; // 2% de comissão do site; ajustar para 0.04 se quiser 4%
              $commissionValue = $grandTotal * $commissionRate;
              $finalTotal = $grandTotal + $commissionValue;
            ?>
            <tr>
              <th colspan="3" class="text-end">Total dos produtos</th>
              <th>$<?= number_format($grandTotal, 2) ?></th>
              <th></th>
            </tr>
            <tr>
              <th colspan="3" class="text-end">Comissão do site (<?= ($commissionRate*100) ?>%)</th>
              <th>$<?= number_format($commissionValue, 2) ?></th>
              <th></th>
            </tr>
            <tr>
              <th colspan="3" class="text-end">Total com Comissão</th>
              <th>$<?= number_format($finalTotal, 2) ?></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
      <a href="../index.php" class="btn btn-primary" style="margin-top: 20px">Continuar comprando</a>
      <div class="d-flex flex-column flex-md-row gap-2 mt-4">  
        <div class="card flex-fill">
          <div class="card-body">
            <h5 class="card-title">Pagar com MBWay</h5>
            <p class="card-text text-muted mb-3">Digite o seu número de telemóvel para realizar o pagamento-</p>
            <form method="POST" class="row g-2 align-items-end">
              <div class="col">
                <label class="form-label" for="mbway_phone">Telemóvel (9 digitos)</label>
                <input id="mbway_phone" name="mbway_phone" type="text" class="form-control" placeholder="912345678" required pattern="\d{9}" maxlength="9">
              </div>
              <div class="col-auto">
                <button type="submit" name="checkout" class="btn btn-success w-100">Pagar com MBWay</button>
              </div>
            </form>
            <p class="text-muted small mt-2 mb-0">Uma simulação. Nenhum pagamento será realizado</p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
