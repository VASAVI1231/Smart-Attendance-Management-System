<?php
//manage_users.php
require_once 'helpers.php';
require_login();
$user = current_user();

if($user['role']!='admin'){
    flash("Access Denied");
    header("Location: dashboard.php");
    exit;
}

$users = $mysqli->query("
    SELECT * FROM users ORDER BY id
")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Users</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

<h1>Manage Users</h1>

<table class="table">
<tr>
<th>ID</th>
<th>Username</th>
<th>Full Name</th>
<th>Role</th>
<th>Email</th>
<th>Branch</th>
<th>Action</th>
</tr>

<?php foreach($users as $u): ?>
<tr>
<td><?=$u['id']?></td>
<td><?=$u['username']?></td>
<td><?=$u['full_name']?></td>
<td><?=$u['role']?></td>
<td><?=$u['email']?></td>
<td><?=$u['branch']?></td>

<td>
  <!-- EDIT -->
  <a href="edit_user.php?id=<?=$u['id']?>">
    <button>Edit</button>
  </a>

  <!-- DELETE -->
  <?php if($u['role']!='admin'): ?>   <!-- admin ni admin delete cheyyakudadhu -->
  <a href="delete_user.php?id=<?=$u['id']?>"
     onclick="return confirm('Delete user <?=$u['full_name']?> ?')">
     <button class="danger">Delete</button>
  </a>
  <?php endif; ?>

</td>
</tr>
<?php endforeach; ?>

</table>

<p><a href="dashboard.php">Back</a></p>
</div>
</body>
</html>