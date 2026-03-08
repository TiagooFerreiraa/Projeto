<?php
	session_start();
	include '../Connection/connection.php';

	if (isset($_POST['Login'])) {
		// ---- Buscar os valores do formulário ----
		$Email = $_POST['Email'];
		$Password = $_POST['Password'];

		// ---- Fazer busca ----
		$sql = "SELECT Password, Is_Admin FROM users WHERE Email = ?";
		$stmt = $connection -> prepare($sql);
		$stmt -> bind_param("s", $Email);
		$stmt -> execute();
		$result = $stmt -> get_result();

		// ---- Se o login funcionar ou não ----
		if ($result && $result -> num_rows > 0) {
			$row = $result -> fetch_assoc();
			$hashedPassword = $row['Password'];
			$isAdmin = $row['Is_Admin'];

			if (password_verify($Password, $hashedPassword)) {
				// ---- Se login for sucesso ----
				$_SESSION['user'] = $Email;
				$_SESSION['Is_Admin'] = $isAdmin;

				if ($isAdmin) {
					header("Location: ../Admin/UserManagement/users_management.php");
				} else {
					header("Location: ../index.php");
				}
				exit();
			} else {
				$error = "Email or password incorrect";
			}
		} else {
			$error = "Email or password incorrect";
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Page</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
			<h2 class="text-center mb-4">Login</h2>
			<form method="POST">
				<div class="mb-3">
					<label for="email" class="form-label">Email Address</label>
					<input type="email" class="form-control" id ="email" name="Email"  required>
				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input type="password" class="form-control" id="password" name="Password" required>
				</div>
				<div class="mb-3 form-check">
					<input type="checkbox" class="form-check-input" id="check">
					<label for="check" class="form-check-labels">Check me out</label>
				</div>
				<button type="submit" class="btn btn-primary w-100" name="Login">Submit</button>
			</form>
			<hr>
			<p class="text-center mb-0">Don't have an account?</p>
			<a href="register.php" class="text-center">Register</a>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>