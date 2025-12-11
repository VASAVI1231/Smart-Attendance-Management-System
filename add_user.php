<?php
//add_user.php
require_once 'helpers.php';
require_login();
$user = current_user();
if($user['role'] != 'admin'){ 
    flash('Access denied'); 
    header('Location: dashboard.php'); 
    exit; 
}

if($_SERVER['REQUEST_METHOD']=='POST'){

    $username = $mysqli->real_escape_string($_POST['username']);
    $password_plain = $_POST['password']; // Store original
    $password = password_hash($password_plain,PASSWORD_DEFAULT);

    $full = $mysqli->real_escape_string($_POST['full_name']);
    $role = $mysqli->real_escape_string($_POST['role']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $branch = $mysqli->real_escape_string($_POST['branch']);

    if($username=='' || $password_plain=='' || $role==''){ 
        flash('Fill required fields'); 
        header('Location:add_user.php'); 
        exit; 
    }

    // INSERT USER
    $stmt = $mysqli->prepare("
        INSERT INTO users (username,password,full_name,role,email,branch) 
        VALUES (?,?,?,?,?,?)
    ");
    $stmt->bind_param('ssssss',$username,$password,$full,$role,$email,$branch);

    if($stmt->execute()){ 
        flash('User created successfully'); 
        header('Location:add_user.php'); 
        exit; 
    }
    else { 
        flash('DB error: '.$mysqli->error); 
        header('Location:add_user.php'); 
        exit; 
    }
}

$branches = $mysqli->query("SELECT * FROM branches ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$users = $mysqli->query("SELECT * FROM users ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add User</title>
<link rel="stylesheet" href="style.css">

<style>
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
label{
    font-weight:bold;
    display:block;
    margin-top:10px;
}
input,select{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
}
button{
    margin-top:14px;
    width:100%;
    background:#0066ff;
    color:white;
    padding:12px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
.button:hover{
    background:#0047b3;
}
</style>

</head>
<body>

<div class="container">

<h1>Add User</h1>

<div class="card">

<form method="post">

    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="text" name="password" required>

    <label>Full Name</label>
    <input type="text" name="full_name">

    <label>Role</label>
    <select name="role" required>
      <option value="admin">Admin</option>
      <option value="hod">HOD</option>
      <option value="class">Class Lecturer</option>
      <option value="lecturer">Lecturer</option>
    </select>

    <label>Email</label>
    <input type="email" name="email">

    <label>Branch</label>
    <select name="branch" required>
      <option value="">-- Select Branch --</option>
      <?php foreach($branches as $b): ?>
        <option value="<?=esc($b['name'])?>"><?=esc($b['name'])?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Create User</button>

</form>
</div>

<h3>Existing Users</h3>
<table class="table">
<tr>
  <th>ID</th>
  <th>Username</th>
  <th>Role</th>
  <th>Full Name</th>
  <th>Email</th>
  <th>Branch</th>
</tr>

<?php foreach($users as $u): ?>
<tr>
  <td><?=esc($u['id'])?></td>
  <td><?=esc($u['username'])?></td>
  <td><?=esc($u['role'])?></td>
  <td><?=esc($u['full_name'])?></td>
  <td><?=esc($u['email'])?></td>
  <td><?=esc($u['branch'])?></td>
</tr>
<?php endforeach; ?>

</table>

<p><a href="dashboard.php">Back</a></p>

</div>

</body>
</html>