<?php
	session_start();
	include 'Connection/connection.php';

	// ---- Caso esteja logado e seja admin ----
	if (isset($_SESSION['Is_Admin']) && $_SESSION['Is_Admin'] == 1) {
    header("Location: Admin/UserManagement/users_management.php");
    exit();
}

	// ---- Caso não esteja logado ----
	if (!isset($_SESSION['user'])) {
		header("Location: Authentication/login.php");
		exit();
	}

	// ---- Buscar categorias da base de dados ----
	$sql = "SELECT id, name, COALESCE(Icon, 'bi-list-ul') AS Icon FROM categories ORDER BY name";
	$result = $connection->query($sql);

	$categories = [];
	if ($result && $result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$categories[] = $row;
		}
	} else {
		echo "No categories found or query failed: " . $connection->error;
	}

	// ---- Buscar produtos da base de dados ----

	$product_sql = "SELECT products.*, categories.Name AS Category_Name, users.Username AS Publisher_Name, users.Phone_Number AS Publisher_Phone 
									FROM products 
									LEFT JOIN categories ON products.Category_ID = categories.ID
									LEFT JOIN users ON products.Publisher_ID = users.ID
									ORDER BY products.ID";
	$product_result = $connection->query($product_sql);

	$products = [];
	
	if ($product_result && $product_result->num_rows > 0) {
		while ($row = $product_result->fetch_assoc()) {
			$products[] = $row;
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Página Inicial</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
	<link rel="icon" type="image/x-icon" href="Images/logoo.png">
	<style>
		body {
			background: url('Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
		}

		/* Garantir que os cartões de produtos formam uma grelha consistente */
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
							<a class="nav-link active" aria-current="page" href="index.php">
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
										<a class="dropdown-item" href="products.php?category_id=<?php echo $cat['id']; ?>">
									<i class="bi <?= htmlspecialchars($cat['Icon'] ?? 'bi-list-ul') ?> me-2"></i>
									<?php echo htmlspecialchars($cat['name']); ?>
								</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
						<li class="nav-item">								<a href="profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Perfil</a>
							</li>
							<li class="nav-item">					<a href="sell_product.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i>Vender um Produto</a>
						</li>
						<li class="nav-item">
							<a href="Authentication/logout.php" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i>Terminar sessão</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</nav>
	<main class="container my-5">
		<div class="row row-cols-1 row-cols-md-3 g-4 mt-4 align-items-stretch">
			<?php if (!empty($products)): ?>
				<?php foreach ($products as $product): ?>
					<div class="col">
						<div class="card h-100">
							<?php
							$imageSrc = 'Images/default.png';
							if (!empty($product['Image'])) {
								// Se a imagem estiver guardada como data URL, usá-la diretamente. Caso contrário, trata-a como caminho de ficheiro.
								if (strpos($product['Image'], 'data:') === 0) {
									$imageSrc = $product['Image'];
								} else {
									$imageSrc = htmlspecialchars($product['Image']);
								}
							}
						?>
						<img src="<?= $imageSrc ?>" class="card-img-top" alt="<?= htmlspecialchars($product['Name']) ?>">
							<div class="card-body">
								<h5 class="card-title"><?= htmlspecialchars($product['Name']) ?></h5>
								<p class="card-text"><?= htmlspecialchars($product['Description']) ?></p>
								<p class="card-text"><strong>Categoria:</strong> <?= htmlspecialchars($product['Category_Name']) ?></p>
						<p class="card-text"><strong>Vendedor:</strong> <?= htmlspecialchars($product['Publisher_Name'] ?? 'Desconhecido') ?> <br><small><strong>Tel:</strong> <?= htmlspecialchars($product['Publisher_Phone'] ?? 'N/D') ?></small></p>
						<p class="card-text"><strong>Preço:</strong> $<?= htmlspecialchars($product['Price']) ?></p>
						<a href="products.php?id=<?= $product['ID'] ?>" class="btn btn-primary">Ver Produto</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<p>Sem produtos encontrados.</p>
			<?php endif; ?>
		</div>
	</main>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>