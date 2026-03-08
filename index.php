<?php
	session_start();
	include 'Connection/connection.php';

	// ---- Caso esteja logado e seja admin ----
	if (!isset($_SESSION['Is_Admin'])) {
		header("Location: Admin/UserManagement/users_management.php");
		exit();
	}

	// ---- Caso não esteja logado ----
	if (!isset($_SESSION['user'])) {
		header("Location: Authentication/login.php");
		exit();
	}

	// ---- Buscar categories da base de dados ----
	$sql = "SELECT id, name FROM categories ORDER BY name";
	$result = $connection->query($sql);

	$categories = [];
	if ($result && $result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$categories[] = $row;
		}
	} else {
		echo "No categories found or query failed: " . $connection->error;
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Main Page</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<style>
		body {
			background: url('Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
		}
	</style>
</head>
</head>
<body>
	<nav class="navbar bg-body-tertiary fixed-top">
		<div class="container-fluid">
			<a class="navbar-brand" href="#">NovusStore</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="offcanvasNavbarLabel">NovusStore</h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
						<li class="nav-item">
							<a class="nav-link active" aria-current="page" href="#">Home</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								Categories
							</a>
							<ul class="dropdown-menu">
								<?php foreach ($categories as $cat): ?>
									<li>
										<a class="dropdown-item" href="products.php?category_id=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
						<li class="nav-item">
							<a href="Authentication/logout.php" class="nav-link">Logout</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</nav>
	<h1 style="text-align: center">Main Page</h1>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>