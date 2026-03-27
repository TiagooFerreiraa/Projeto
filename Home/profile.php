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

// Obter dados do usuário
$stmt = $connection->prepare('SELECT Username, Email, Phone_Number, Balance FROM users WHERE ID = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $error = 'Utilizador não encontrado.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['Username'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $phone = trim($_POST['Phone_Number'] ?? '');
    $newPassword = trim($_POST['Password'] ?? '');

    if ($username === '' || $email === '') {
        $error = 'Nome de utilizador e email não podem ficar em branco.';
    } elseif ($phone !== '' && !preg_match('/^9\d{8}$/', $phone)) {
        $error = 'Número de telemóvel inválido. Deve ter 9 dígitos e iniciar por 9.';
    } else {
        // Verificar se email está em uso por outro user
        $stmt = $connection->prepare('SELECT ID FROM users WHERE Email = ? AND ID != ?');
        $stmt->bind_param('si', $email, $userId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Este email já está associado a outro utilizador.';
        } else {
            $stmt->close();

            if ($newPassword !== '') {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $connection->prepare('UPDATE users SET Username = ?, Email = ?, Phone_Number = ?, Password = ? WHERE ID = ?');
                $stmt->bind_param('ssssi', $username, $email, $phone, $hashedPassword, $userId);
            } else {
                $stmt = $connection->prepare('UPDATE users SET Username = ?, Email = ?, Phone_Number = ? WHERE ID = ?');
                $stmt->bind_param('sssi', $username, $email, $phone, $userId);
            }

            if ($stmt->execute()) {
                $message = 'Perfil atualizado com sucesso.';
                $_SESSION['user'] = $username;
                // atualizar dados exibidos
                $user['Username'] = $username;
                $user['Email'] = $email;
                $user['Phone_Number'] = $phone;
            } else {
                $error = 'Falha ao atualizar perfil: ' . $connection->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
			background: url('../Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
		}
  </style>
</head>
<body class="container mt-5">
  <h2 class="mb-4">Perfil de Utilizador</h2>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <p><strong>Saldo disponível:</strong> €<?= number_format(floatval($user['Balance']), 2) ?></p>
  <p><a href="withdraw.php" class="btn btn-outline-primary btn-sm">Ir para Saque</a></p>

  <form method="POST">
    <input type="hidden" name="update_profile" value="1">
    <div class="mb-3">
      <label class="form-label">Nome de Utilizador</label>
      <input class="form-control" name="Username" value="<?= htmlspecialchars($user['Username']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" name="Email" value="<?= htmlspecialchars($user['Email']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Nº Telemóvel</label>
      <input type="text" class="form-control" name="Phone_Number" value="<?= htmlspecialchars($user['Phone_Number']) ?>" maxlength="9" placeholder="912345678">
    </div>
    <div class="mb-3">
      <label class="form-label">Alterar Palavra-Passe (opcional)</label>
      <input type="password" class="form-control" name="Password" placeholder="Nova palavra-passe">
    </div>
    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="../index.php" class="btn btn-secondary">Voltar</a>
  </form>

</body>
</html>
