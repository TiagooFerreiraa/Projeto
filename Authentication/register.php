<?php
	session_start();
	include '../Connection/connection.php';

	if (isset($_POST['Register'])) {
		// ---- Buscar os valores do formulário ----
		$Username = $_POST['Username'];
		$Email = $_POST['Email'];
		$Password = $_POST['Password'];

		$HashedPassword = password_hash($Password, PASSWORD_DEFAULT);

		// ---- Verificar se o email já existe ----
		$check = $connection -> prepare("SELECT ID FROM users WHERE Email = ?");
		$check -> bind_param("s", $Email);
		$check -> execute();
		$check -> store_result();

		if ($check -> num_rows > 0) {
			$error = "This email already exists";
		} else {
			$stmt = $connection -> prepare("INSERT INTO users (Username, Email, Password) VALUES (?, ?, ?)");
			$stmt -> bind_param("sss", $Username, $Email, $HashedPassword);

			if ($stmt->execute()) {
				$_SESSION['user'] = $Email;
				header("Location: index.php");
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
	<title>Register Page</title>
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
			<h2 class="text-center mb-4">Register</h2>
			<form method="POST">
				<div class="mb-3">
					<label for="username" class="form-label">Username</label>
					<input type="text" class="form-control" id ="username" name="Username"  required>
				</div>
				<div class="mb-3">
					<label for="email" class="form-label">Email Address</label>
					<input type="email" class="form-control" id ="email" name="Email"  required>
				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input type="password" class="form-control" id="password" name="Password" required>
				</div>
				<button type="submit" class="btn btn-primary w-100" name="Register">Register</button>
			</form>
			<hr>
			<p class="text-center mb-0">Already have an account?</p>
			<a href="login.php" class="text-center">Login</a>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>