<?php
  include '../../Connection/connection.php';

  $id = $_GET['id'];

  $sql = "SELECT * FROM users WHERE ID = $id";
  $result = mysqli_query($connection, $sql);
  $user = mysqli_fetch_assoc($result);
?>
<form method="POST" action="update_user.php">
  <input type="hidden">

  <div class="mb-3">
    <label>Username</label>
    <input type="text" name="Username" class="form-control" value="<?php echo $user['username']; ?>">
  </div>
</form>