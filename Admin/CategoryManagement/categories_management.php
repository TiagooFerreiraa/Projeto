<?php
  session_start();
  include '../../Connection/connection.php';

  // ---- Ver se utilizador está logado ----
  if (!isset($_SESSION['user'])) {
    header("Location: ../../Authentication/login.php");
    exit();
  }

  // ---- Ver se utilizador é admin ----
  /* if (!isset($_SESSION['Is_Admin']) || $_SESSION['Is_Admin'] !== true) {
    echo "Access denied.";
    exit();
  } */

	// ---- Buscar users da base de dados ----
	$limit = 10;
	$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
	if ($page < 1) {
		$page = 1;
	}

	$offset = ($page - 1) * $limit;

	$count_sql = "SELECT COUNT(*) AS total FROM categories";
	$count_result = $connection->query($count_sql);
	$count_row = $count_result->fetch_assoc();

	$total_categories = $count_row['total'];
	$total_pages = ceil($total_categories / $limit);

	$sql = "SELECT * FROM categories ORDER BY ID LIMIT $limit OFFSET $offset";
	$result = $connection->query($sql);
?>  

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Administration</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
		<style>
			body {
				background: url('../../Images/main_bg.png') no-repeat center center fixed;
				background-size: cover;
			}
		</style>
	</head>
	<body>
		<nav class="navbar bg-body-tertiary fixed-top">
			<div class="container-fluid">
				<a class="navbar-brand" href="#">NovusStore Administration</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
					<div class="offcanvas-header">
						<h5 class="offcanvas-title" id="offcanvasNavbarLabel">NovusStore Administration - <?= htmlspecialchars($_SESSION['user']) ?></h5>
						<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
					</div>
					<div class="offcanvas-body">
						<ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									Administration
								</a>
								<ul class="dropdown-menu">
									<li><a href="../UserManagement/users_management.php" class="dropdown-item">Users</a></li>
									<li><a href="../ProductManagement/products_management.php" class="dropdown-item">Products</a></li>
									<li><a href="categories_management.php" class="dropdown-item active">Categories</a></li>
								</ul>
							</li>
							<li class="nav-item">
								<a href="../../Authentication/logout.php" class="nav-link">Logout</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</nav>
		<main class="container my-5">
			<h2 class="mb-4 text-center">Categories Management</h2>

			<div class="table-responsive">
				<table class="table table-striped table-bordered align-middle">
					<thead class="table-dark text-center">
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Description</th>
							<th>Creation Date</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($result->num_rows > 0): ?>
							<?php while ($category = $result->fetch_assoc()): ?>
								<tr>
									<td class="text-center"><?= htmlspecialchars($category['ID']) ?></td>
									<td class="text-center"><?= htmlspecialchars($category['Name']) ?></td>
									<td class="text-center"><?= htmlspecialchars($category['Description']) ?></td>
									<td class="text-center"><?= htmlspecialchars($category['Created_At']) ?></td>
									<td class="text-center">
										<a href="edit_category.php?id=<?php echo $category['ID']; ?>" class="btn btn-warning btn-sm">Edit</a>
										<a href="delete_category.php?id=<?php echo $category['ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this Category?');">Delete</a>
									</td>
								</tr>
							<?php endwhile; ?>
						<?php else: ?>
							<tr>
								<td colspan="5" class="text-center">No users found.</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<a href="add_category.php" class="btn btn-success mb-3">Add new category</a>
			<nav>
				<ul class="pagination justify-content-center">
					<?php if ($page > 1): ?>
						<li class="page-item">
							<a class="page-link" href="?page=<?php $page-1 ?>">Previous</a>
						</li>
					<?php endif; ?>

					<?php for ($i = 1; $i <= $total_pages; $i++): ?>
						<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
							<a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
						</li>
					<?php endfor; ?>

					<?php if ($page < $total_pages): ?>
						<li class="page-item">
							<a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
						</li>
					<?php endif; ?>
				</ul>
			</nav>
		</main>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	</body>
</html>