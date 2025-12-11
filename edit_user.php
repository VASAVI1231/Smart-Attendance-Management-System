<?php
//edit_user.php
require_once 'helpers.php';
require_login();
$user = current_user();

if($user['role']!='admin'){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if(!$id){
    flash("Invalid user");
    header("Location: manage_users.php");
    exit;
}

$u = $mysqli->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
if(!$u){
    flash("User not found");
    header("Location: manage_users.php");
    exit;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $username = $mysqli->real_escape_string($_POST['username']);
    $full_name = $mysqli->real_escape_string($_POST['full_name']);
    $role = $mysqli->real_escape_string($_POST['role']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $branch = $mysqli->real_escape_string($_POST['branch']);

    // If password entered → hash and update
    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $mysqli->query("
            UPDATE users 
            SET username='$username',
                full_name='$full_name',
                role='$role',
                email='$email',
                branch='$branch',
                password='$password'
            WHERE id=$id
        ");
    } 
    else {
        // Password untouched
        $mysqli->query("
            UPDATE users 
            SET username='$username',
                full_name='$full_name',
                role='$role',
                email='$email',
                branch='$branch'
            WHERE id=$id
        ");
    }

    flash("User updated successfully!");
    header("Location: manage_users.php");
    exit;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit User</title>
<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
<h1>Edit User</h1>

<form method="post">

<label>Username</label>
<input type="text" name="username" value="<?=$u['username']?>" required>

<label>Full Name</label>
<input type="text" name="full_name" value="<?=$u['full_name']?>">

<label>Email</label>
<input type="email" name="email" value="<?=$u['email']?>">

<label>Branch</label>
<input type="text" name="branch" value="<?=$u['branch']?>">

<label>Role</label>
<select name="role">
  <option value="admin" <?=$u['role']=='admin'?'selected':''?>>Admin</option>
  <option value="hod" <?=$u['role']=='hod'?'selected':''?>>HOD</option>
  <option value="class" <?=$u['role']=='class'?'selected':''?>>Class Lecturer</option>
  <option value="lecturer" <?=$u['role']=='lecturer'?'selected':''?>>Lecturer</option>
</select>

<label>New Password (Optional)</label>
<input type="password" name="password" placeholder="Leave blank to keep old password">

<button type="submit">Update User</button>

</form>

<p><a href="manage_users.php">Back</a></p>

</div>
</body>
</html>