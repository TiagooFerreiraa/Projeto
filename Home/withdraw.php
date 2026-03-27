<?php
session_start();
include '../Connection/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

$userId = intval($_SESSION['user_id']);
$balance = 0;
$message = '';
$error = '';

$stmt = $connection->prepare('SELECT Balance FROM users WHERE ID = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $balance = floatval($row['Balance']);
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $amount = floatval($_POST['amount']);
    if ($amount <= 0) {
        $error = 'Valor inválido.';
    } elseif ($amount > $balance) {
        $error = 'Saldo insuficiente.';
    } else {
        $newBalance = $balance - $amount;
        $stmt = $connection->prepare('UPDATE users SET Balance = ? WHERE ID = ?');
        $stmt->bind_param('di', $newBalance, $userId);
        if ($stmt->execute()) {
            $message = 'Saque simulado realizado com sucesso: €' . number_format($amount, 2) . '. New balance: €' . number_format($newBalance, 2) . '.';
            $balance = $newBalance;
        } else {
            $error = 'Falha ao processar saque.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Saque</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <style>
    body {
			background: url('../Images/main_bg.png') no-repeat center center fixed;
			background-size: cover;
		}
  </style>
</head>
<body class="container mt-5">
  <h2 class="mb-4">Saque de Saldo</h2>
  <p>Saldo disponível: <strong>€<?= number_format($balance, 2) ?></strong></p>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="mb-3">
    <div class="mb-3">
      <label class="form-label">Valor para levantar</label>
      <input type="number" step="0.01" min="0.01" max="<?= $balance ?>" class="form-control" name="amount" required>
    </div>
    <button type="submit" name="withdraw" class="btn btn-success">Sacar</button>
    <a href="../index.php" class="btn btn-secondary">Voltar</a>
  </form>
</body>
</html>