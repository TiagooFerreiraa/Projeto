<?php
	session_start();
	include 'Connection/connection.php';

	// ---- Caso esteja logado e seja admin ----
	if (isset($_SESSION['Is_Admin']) && $_SESSION['Is_Admin'] == 1) {
    header("Location: Admin/UserManagement/users_management.php");
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
			background: #f8f9fa;
			margin-top: 0;
		}

		main {
			width: 100%;
			margin-top: 75px;
		}

		/* Navbar styling */
		.navbar {
			box-shadow: 0 4px 12px rgba(0,0,0,0.08);
			background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
			border-bottom: 1px solid #e9ecef;
		}

		.navbar-brand {
			font-weight: 700;
			font-size: 1.5rem;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
			letter-spacing: 0.5px;
		}

		.nav-link {
			font-weight: 500;
			color: #495057 !important;
			margin: 0 5px;
			padding: 8px 12px !important;
			border-radius: 5px;
			transition: all 0.3s ease;
		}

		.nav-link:hover {
			color: #667eea !important;
			background: rgba(102, 126, 234, 0.1);
		}

		.navbar-nav .nav-link.active {
			color: #667eea !important;
		}

		.dropdown-menu {
			border: none;
			box-shadow: 0 8px 16px rgba(0,0,0,0.1);
			border-radius: 8px;
			padding: 10px 0;
		}

		.dropdown-item {
			padding: 10px 20px;
			transition: all 0.2s ease;
			border-left: 3px solid transparent;
		}

		.dropdown-divider {
			margin: 8px 0;
			border-color: #e9ecef;
		}

		.dropdown-item:hover {
			background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
			border-left-color: #667eea;
			color: #667eea;
		}

		/* Menu do utilizador */
		.user-menu {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-left: auto;
		}

		/* Carrossel de Banners */
		#bannerWrapper {
			display: flex;
			justify-content: center;
			align-items: center;
			max-width: 1200px;
			margin: 20px auto;
			border-radius: 15px;
			overflow: hidden;
		}

		#bannerCarousel {
			box-shadow: 0 10px 40px rgba(0,0,0,0.1);
			width: 100%;
		}

		#bannerCarousel .carousel-item {
			height: 320px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			text-align: center;
			display: none;
		}

		#bannerCarousel .carousel-item.active {
			display: flex;
			align-items: center;
			justify-content: center;
		}

		#bannerCarousel .carousel-item:nth-child(2) {
			background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
		}

		#bannerCarousel .carousel-item:nth-child(3) {
			background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
		}

		#bannerCarousel .carousel-item:nth-child(4) {
			background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
		}

		#bannerCarousel .carousel-item h2 {
			font-size: 2.2rem;
			font-weight: 700;
			margin: 0 0 15px 0;
			text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
		}

		#bannerCarousel .carousel-item p {
			font-size: 1.05rem;
			text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
			max-width: 500px;
			margin: 0 auto 25px;
		}

		#bannerCarousel .carousel-item .btn {
			padding: 10px 30px;
			font-size: 1rem;
			font-weight: 600;
			border-radius: 8px;
			background: white;
			color: #333;
			border: none;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			text-decoration: none;
		}

		#bannerCarousel .carousel-item .btn:hover {
			background: #f8f9fa;
			color: #333;
		}

		#bannerCarousel .carousel-control-prev,
		#bannerCarousel .carousel-control-next {
			width: 50px;
			height: 50px;
			background: rgba(0, 0, 0, 0.3);
			border-radius: 50%;
			top: 50%;
			transform: translateY(-50%);
		}

		#bannerCarousel .carousel-control-prev:hover,
		#bannerCarousel .carousel-control-next:hover {
			background: rgba(0, 0, 0, 0.5);
		}

		#bannerCarousel .carousel-indicators {
			bottom: 20px;
		}

		#bannerCarousel .carousel-indicators button {
			width: 12px;
			height: 12px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.5);
			border: none;
		}

		#bannerCarousel .carousel-indicators button.active {
			background: white;
		}

		@media (max-width: 768px) {
			#bannerCarousel .carousel-item {
				height: 250px;
			}

			#bannerCarousel .carousel-item h2 {
				font-size: 1.5rem;
				margin-bottom: 10px;
			}

			#bannerCarousel .carousel-item p {
				font-size: 0.9rem;
				margin-bottom: 15px;
			}

			#bannerCarousel .btn {
				padding: 8px 20px;
				font-size: 0.9rem;
			}

			#bannerCarousel .carousel-control-prev,
			#bannerCarousel .carousel-control-next {
				width: 40px;
				height: 40px;
			}
		}

		/* Secção de Categorias */
		.categories-section {
			margin: 50px 0;
			padding: 40px 0;
		}

		.categories-section h2 {
			text-align: center;
			margin-bottom: 40px;
			color: #333;
			font-weight: 700;
			font-size: 2rem;
		}

		.category-card {
			text-align: center;
			padding: 25px 15px;
			border-radius: 12px;
			transition: all 0.3s ease;
			background: white;
			border: 2px solid #e9ecef;
			text-decoration: none;
			color: #333;
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
			height: 100%;
			min-height: 150px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.05);
			margin: 0 auto;
		}

		.category-card:hover {
			transform: translateY(-8px);
			box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
			border-color: #667eea;
			background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
		}

		.category-card i {
			font-size: 2.8rem;
			color: #667eea;
			margin-bottom: 12px;
			transition: all 0.3s ease;
		}

		.category-card:hover i {
			color: #764ba2;
			transform: scale(1.1);
		}

		.category-card p {
			margin: 0;
			font-weight: 600;
			font-size: 0.95rem;
		}

		/* Secção de Produtos em Destaque */
		.featured-section {
			margin: 50px 0;
			padding: 40px 0;
		}

		.featured-section h2 {
			text-align: center;
			margin-bottom: 40px;
			color: #333;
			font-weight: 700;
			font-size: 2rem;
		}

		.product-card {
			transition: all 0.3s ease;
			height: 100%;
			border: 1px solid #e9ecef;
			border-radius: 12px;
			overflow: hidden;
			background: white;
			box-shadow: 0 2px 8px rgba(0,0,0,0.05);
		}

		.product-card:hover {
			transform: translateY(-10px);
			box-shadow: 0 16px 32px rgba(102, 126, 234, 0.15);
			border-color: #667eea;
		}

		.product-card .card-img-top {
			height: 160px;
			object-fit: cover;
		}

		.product-card .card-body {
			padding: 12px;
			display: flex;
			flex-direction: column;
		}

		.product-card .card-title {
			font-weight: 600;
			color: #333;
			font-size: 0.9rem;
			margin-bottom: 6px;
			line-height: 1.3;
			min-height: 2.2em;
		}

		.product-card .card-text {
			font-size: 0.8rem;
			color: #6c757d;
			margin-bottom: 4px;
		}

		.product-price {
			font-size: 1.1rem;
			font-weight: 700;
			color: #667eea;
			margin: 6px 0;
		}

		.product-card .btn {
			margin-top: auto;
			padding: 6px 12px;
			font-size: 0.85rem;
		}

		.btn-primary {
			background: #667eea;
			border: none;
			border-radius: 6px;
			font-weight: 600;
			transition: all 0.3s ease;
		}

		.btn-primary:hover {
			background: #764ba2;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
		}

		/* Secção Footer com CTA */
		.cta-section {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 60px 30px;
			text-align: center;
			border-radius: 15px;
			margin: 60px auto;
			max-width: 100%;
			box-shadow: 0 15px 40px rgba(102, 126, 234, 0.25);
		}

		.cta-section h2 {
			font-size: 2.2rem;
			margin-bottom: 20px;
			font-weight: 700;
		}

		.cta-section p {
			font-size: 1.1rem;
			margin-bottom: 30px;
			opacity: 0.95;
		}

		.cta-section .btn-light {
			padding: 12px 35px;
			font-weight: 600;
			border-radius: 8px;
			transition: all 0.3s ease;
			box-shadow: 0 4px 12px rgba(0,0,0,0.1);
		}

		.cta-section .btn-light:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(0,0,0,0.15);
			background: #f0f0f0;
		}

		.no-products {
			text-align: center;
			padding: 60px 20px;
			color: #999;
		}

		/* Container centralização */
		.container {
			max-width: 1200px !important;
			margin-left: auto !important;
			margin-right: auto !important;
		}

		.row {
			margin-left: auto !important;
			margin-right: auto !important;
			width: 100%;
		}

		.col-6, .col-sm-4, .col-md-3, .col-lg-2 {
			display: flex !important;
			justify-content: center !important;
		}

		/* Responsividade melhorada */
		@media (max-width: 992px) {
			.categories-section h2,
			.featured-section h2 {
				font-size: 1.7rem;
			}

			.cta-section h2 {
				font-size: 1.8rem;
			}

			.cta-section p {
				font-size: 1rem;
			}
		}

		@media (max-width: 576px) {
			.navbar-brand {
				font-size: 1.2rem;
			}

			.categories-section h2,
			.featured-section h2 {
				font-size: 1.4rem;
				margin-bottom: 25px;
			}

			.cta-section {
				padding: 40px 20px;
				margin: 40px auto;
				border-radius: 12px;
			}

			.cta-section h2 {
				font-size: 1.5rem;
				margin-bottom: 15px;
			}

			.cta-section p {
				font-size: 0.95rem;
				margin-bottom: 20px;
			}

			.cta-section .btn-light {
				padding: 10px 25px;
				font-size: 0.9rem;
			}
		}
	</style>
</head>
<body>
	<nav class="navbar bg-body-tertiary fixed-top navbar-expand-lg">
		<div class="container-fluid ps-4 pe-4">
			<a class="navbar-brand fw-bold" href="index.php">
				<i class="bi bi-shop me-2"></i>NovusStore
			</a>
			<button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
				<div class="offcanvas-header border-bottom">
					<h5 class="offcanvas-title" id="offcanvasNavbarLabel">
						Menu
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
						<li class="nav-item">
							<a class="nav-link active" aria-current="page" href="index.php">
								<i class="bi bi-house-door-fill me-2"></i>Inicio
							</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-list-ul me-2"></i>Categorias
							</a>
							<ul class="dropdown-menu">
								<?php foreach ($categories as $cat): ?>
									<li>
										<a class="dropdown-item" href="Home/products.php?category_id=<?php echo $cat['id']; ?>">
											<i class="bi <?= htmlspecialchars($cat['Icon'] ?? 'bi-list-ul') ?> me-2"></i>
											<?php echo htmlspecialchars($cat['name']); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
						<?php if (isset($_SESSION['user'])): ?>
							<li class="nav-item">
								<a class="nav-link" href="Cart/cart.php">
									<i class="bi bi-cart-fill me-2"></i>Carrinho
								</a>
							</li>
							<li class="nav-item">					
								<a href="Home/sell_product.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i>Vender
								</a>
							</li>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="bi bi-person-circle me-2"></i>Minha Conta
								</a>
								<ul class="dropdown-menu">
									<li>
										<a class="dropdown-item" href="Home/profile.php">
											<i class="bi bi-person me-2"></i>Perfil
										</a>
									</li>
									<li>
										<a class="dropdown-item" href="Home/my_products.php">
											<i class="bi bi-card-checklist me-2"></i>Meus Produtos
										</a>
									</li>
									<li><hr class="dropdown-divider"></li>
									<li>
										<a class="dropdown-item" href="Authentication/logout.php">
											<i class="bi bi-box-arrow-right me-2"></i>Terminar sessão
										</a>
									</li>
								</ul>
							</li>
						<?php else: ?>
							<li class="nav-item">
								<a href="Authentication/login.php" class="nav-link"><i class="bi bi-box-arrow-in-right me-2"></i>Entrar</a>
							</li>
							<li class="nav-item">
								<a href="Authentication/register.php" class="nav-link"><i class="bi bi-person-plus me-2"></i>Registar</a>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</nav>
	<main>
		<!-- Carrossel de Banners Promocionais -->
		<div id="bannerWrapper" style="margin: 20px; border-radius: 15px; overflow: hidden;">
			<div id="bannerCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
				<div class="carousel-indicators">
					<button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active"></button>
					<button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1"></button>
					<button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2"></button>
					<button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="3"></button>
				</div>
				<div class="carousel-inner">
					<div class="carousel-item active">
						<h2>Bem-vindo à NovusStore!</h2>
						<p>Descubra os melhores produtos com os melhores preços</p>
						<a href="#produtos-destaque" class="btn btn-light">Ver Produtos</a>
					</div>
					<div class="carousel-item">
						<h2>🎉 Promoção Especial!</h2>
						<p>Até 50% de desconto em Eletrónicos</p>
						<a href="Home/products.php?category_id=2" class="btn btn-light">Explorar Eletrónicos</a>
					</div>
					<div class="carousel-item">
						<h2>✨ Vestuário em Destaque</h2>
						<p>As melhores coleções de moda agora disponíveis</p>
						<a href="Home/products.php?category_id=1" class="btn btn-light">Ver Coleção</a>
					</div>
					<div class="carousel-item">
						<h2>🛍️ Torne-se Vendedor</h2>
						<p>Comece a vender seus produtos agora mesmo</p>
						<?php if (isset($_SESSION['user'])): ?>
							<a href="Home/sell_product.php" class="btn btn-light">Vender Produto</a>
						<?php else: ?>
							<a href="Authentication/register.php" class="btn btn-light">Registar Agora</a>
						<?php endif; ?>
					</div>
				</div>
				<button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon"></span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
					<span class="carousel-control-next-icon"></span>
				</button>
			</div>
		</div>

		<!-- Secção de Categorias -->
		<div class="container categories-section">
			<h2>Explorar Categorias</h2>
			<div class="row g-4 w-100 mx-auto">
				<?php foreach ($categories as $cat): ?>
					<div class="col-6 col-sm-4 col-md-3 col-lg-2 d-flex justify-content-center">
						<a href="Home/products.php?category_id=<?= htmlspecialchars($cat['id']) ?>" class="category-card text-decoration-none" style="width: 100%; max-width: 140px;">
							<i class="bi <?= htmlspecialchars($cat['Icon'] ?? 'bi-list-ul') ?>"></i>
							<p><?= htmlspecialchars($cat['name']) ?></p>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Secção de Produtos em Destaque -->
		<div class="container featured-section" id="produtos-destaque">
			<h2>Produtos em Destaque</h2>
			<?php if (!empty($products)): ?>
				<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 w-100 mx-auto">
					<?php 
					// Mostrar apenas os primeiros 8 produtos
					$featured_products = array_slice($products, 0, 8);
					foreach ($featured_products as $product): 
					?>
						<div class="col d-flex justify-content-center">
							<div class="card product-card h-100" style="width: 100%; max-width: 200px;">
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
									<p class="card-text"><small><?= substr(htmlspecialchars($product['Description']), 0, 60) ?>...</small></p>
									<p class="product-price">€<?= number_format($product['Price'], 2, ',', '.') ?></p>
									<a href="Home/products.php?id=<?= $product['ID'] ?>" class="btn btn-primary mt-auto btn-sm">Ver Produto</a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="no-products">
					<p>Nenhum produto disponível no momento.</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Secção Call to Action -->
		<?php if (!isset($_SESSION['user'])): ?>
			<div class="container cta-section">
				<h2>Junte-se à Nossa Comunidade</h2>
				<p>Registe-se agora e comece a explorar milhares de produtos incríveis</p>
				<a href="Authentication/register.php" class="btn btn-light btn-lg">Registar Agora</a>
			</div>
		<?php endif; ?>
	</main>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>