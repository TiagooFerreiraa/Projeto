<?php
	session_start();
	include '../Connection/connection.php';

	if (isset($_POST['Register'])) {
		// ---- Buscar os valores do formulário ----
		$Username = $_POST['Username'];
		$Email = $_POST['Email'];
		$Phone_Number = $_POST['Phone_Number'];
		$Password = $_POST['Password'];

		$HashedPassword = password_hash($Password, PASSWORD_DEFAULT);

		// ---- Verificar se o email já existe ----
		$check = $connection -> prepare("SELECT ID FROM users WHERE Email = ?");
		$check -> bind_param("s", $Email);
		$check -> execute();
		$check -> store_result();

		if (!preg_match('/^9[0-9]{8}$/', $Phone_Number)) {
			$error = "Número de telemóvel inválido.";
		} elseif ($check -> num_rows > 0) {
			$error = "Este email já existe";
		} else {
			$stmt = $connection -> prepare("INSERT INTO users (Username, Email, Phone_Number, Password) VALUES (?, ?, ?, ?)");
			$stmt -> bind_param("ssis", $Username, $Email, $Phone_Number, $HashedPassword);

			if ($stmt->execute()) {
				$_SESSION['user_id'] = $connection->insert_id;
				$_SESSION['user'] = $Email;
				header("Location: login.php");
				exit();
			} else {
				$error = "Erro ao criar conta";
			}

			$stmt -> close();
		}
	}

	// ---- Buscar categorias da base de dados ----
	$sql = "SELECT id, name, COALESCE(Icon, 'bi-list-ul') AS Icon FROM categories ORDER BY name";
	$result = $connection->query($sql);

	$categories = [];
	if ($result && $result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$categories[] = $row;
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Registar - NovusStore</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
	<link rel="icon" type="image/x-icon" href="../Images/logoo.png">
	<style>
		body {
			background: #f8f9fa;
			margin-top: 0;
		}

		main {
			width: 100%;
			margin-top: 75px;
			min-height: 100vh;
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

		.dropdown-item:hover {
			background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
			border-left-color: #667eea;
			color: #667eea;
		}

		.dropdown-divider {
			margin: 8px 0;
			border-color: #e9ecef;
		}

		/* Register Card */
		.auth-container {
			display: flex;
			justify-content: center;
			align-items: center;
			padding: 40px 20px;
		}

		.auth-card {
			background: white;
			border-radius: 15px;
			box-shadow: 0 10px 40px rgba(0,0,0,0.1);
			padding: 40px;
			width: 100%;
			max-width: 420px;
		}

		.auth-card h2 {
			font-size: 2rem;
			font-weight: 700;
			margin-bottom: 30px;
			color: #333;
			text-align: center;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
		}

		.form-label {
			font-weight: 600;
			color: #495057;
			margin-bottom: 10px;
		}

		.form-control {
			border: 1px solid #e9ecef;
			border-radius: 8px;
			padding: 10px 15px;
			transition: all 0.3s ease;
		}

		.form-control:focus {
			border-color: #667eea;
			box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
		}

		.btn-primary {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			border-radius: 8px;
			padding: 12px;
			font-weight: 600;
			font-size: 1rem;
			transition: all 0.3s ease;
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
		}

		.auth-footer {
			text-align: center;
			margin-top: 25px;
			border-top: 1px solid #e9ecef;
			padding-top: 25px;
		}

		.auth-footer p {
			color: #6c757d;
			margin-bottom: 10px;
		}

		.auth-footer a {
			color: #667eea;
			text-decoration: none;
			font-weight: 600;
			transition: all 0.3s ease;
		}

		.auth-footer a:hover {
			color: #764ba2;
		}

		.alert-danger {
			border-radius: 8px;
			border: 1px solid #f5c6cb;
			margin-bottom: 20px;
		}

		@media (max-width: 576px) {
			.auth-card {
				padding: 30px 20px;
			}

			.auth-card h2 {
				font-size: 1.5rem;
			}
		}
	</style>
</head>
<body>
	<nav class="navbar bg-body-tertiary fixed-top navbar-expand-lg">
		<div class="container-fluid ps-4 pe-4">
			<a class="navbar-brand fw-bold" href="../index.php">
				<i class="bi bi-shop me-2"></i>NovusStore
			</a>
			<button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
				<div class="offcanvas-header border-bottom">
					<h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
						<li class="nav-item">
							<a class="nav-link" href="../index.php">
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
										<a class="dropdown-item" href="../Home/products.php?category_id=<?php echo $cat['id']; ?>">
											<i class="bi <?= htmlspecialchars($cat['Icon'] ?? 'bi-list-ul') ?> me-2"></i>
											<?php echo htmlspecialchars($cat['name']); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
						<li class="nav-item">
							<a href="login.php" class="nav-link"><i class="bi bi-box-arrow-in-right me-2"></i>Entrar</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</nav>

	<main>
		<div class="auth-container">
			<div class="auth-card">
				<h2>Criar Conta</h2>
				<?php if (isset($error)): ?>
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<?php echo htmlspecialchars($error); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>
				<form method="POST">
					<div class="mb-3">
						<label for="username" class="form-label">Nome de Utilizador</label>
						<input type="text" class="form-control" id="username" name="Username" required placeholder="Ex: exemplo123">
					</div>
					<div class="mb-3">
						<label for="email" class="form-label">Endereço de Email</label>
						<input type="email" class="form-control" id="email" name="Email" required placeholder="Ex: exemplo@gmail.com">
					</div>
					<div class="mb-3">
						<label for="phone_number" class="form-label">Número de Telemóvel</label>
						<input type="tel" class="form-control" id="phone_number" name="Phone_Number" maxlength="9" pattern="9[0-9]{8}" required placeholder="Ex: 912345678">
					</div>
					<div class="mb-3">
						<label for="password" class="form-label">Palavra-Passe</label>
						<input type="password" class="form-control" id="password" name="Password" required placeholder="Introduza uma palavra-passe">
					</div>
					<button type="submit" class="btn btn-primary w-100" name="Register">Registar</button>
				</form>
				<div class="auth-footer">
					<p>Já tens uma conta?</p>
					<a href="login.php">Inicia sessão agora</a>
				</div>
			</div>
		</div>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>