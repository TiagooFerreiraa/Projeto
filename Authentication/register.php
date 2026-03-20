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
			echo "Número de telemóvel inválido.";
			exit();
		}

		if ($check -> num_rows > 0) {
			$error = "This email already exists";
		} else {
			$stmt = $connection -> prepare("INSERT INTO users (Username, Email, Phone_Number, Password) VALUES (?, ?, ?, ?)");
			$stmt -> bind_param("ssis", $Username, $Email, $Phone_Number, $HashedPassword);

			if ($stmt->execute()) {
				$_SESSION['user_id'] = $connection->insert_id;
				$_SESSION['user'] = $Email;
				header("Location: login.php");
				exit();
			} else {
				$error = "Error creating account";
			}

			$stmt -> close();
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Registro</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="icon" type="image/x-icon" href="../Images/logoo.png">
	<style>
		body {
			background: url('../Images/bg.png') no-repeat center center fixed;
			background-size: cover;
		}

		#card {
			border-radius: 15px;
		}
	</style>
</head>
<body>
	<div class="container vh-100 d-flex justify-content-center align-items-center">
		<div class="card p-4 shadow" style="width: 400px" id="card">
			<h2 class="text-center mb-4">Registro</h2>
			<form method="POST">
				<div class="mb-3">
					<label for="username" class="form-label">Nome de Utilizador</label>
					<input type="text" class="form-control" id ="username" name="Username" required placeholder="Ex :exemplo123">
				</div>
				<div class="mb-3">
					<label for="email" class="form-label">Endereço de Email</label>
					<input type="email" class="form-control" id ="email" name="Email" required placeholder="Ex: exemplo@gmail.com">
				</div>
				<div class="mb-3">
					<label for="phone_number" class="form-label">Número de Telemóvel</label>
					<input type="tel" class="form-control" id ="phone_number" name="Phone_Number" maxlength="9" pattern="9[0-9]{8}" required placeholder="Ex: 912345678">
				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Palavra-Passe</label>
					<input type="password" class="form-control" id="password" name="Password" required placeholder="Introduza uma palavra-passe">
				</div>
				<button type="submit" class="btn btn-primary w-100" name="Register">Registar</button>
			</form>
			<hr>
			<p class="text-center mb-0">Já tens uma conta?</p>
			<a href="login.php" class="text-center">Iniciar sessão</a>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>