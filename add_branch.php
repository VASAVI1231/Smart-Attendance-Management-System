<?php
//add_branch.php
require_once 'helpers.php';
require_login();
$user = current_user();
if($user['role'] != 'admin'){ flash('Access denied'); header('Location: dashboard.php'); exit; }

if($_SERVER['REQUEST_METHOD']=='POST'){
    $name = $mysqli->real_escape_string($_POST['name']);
    if($name==''){ flash('Branch name required'); header('Location:add_branch.php'); exit; }
    $stmt = $mysqli->prepare("INSERT INTO branches (name) VALUES (?)");
    $stmt->bind_param('s',$name);
    if($stmt->execute()){ flash('Branch added'); header('Location:add_branch.php'); exit; }
    else { flash('DB error: '.$mysqli->error); header('Location:add_branch.php'); exit; }
}

$branches = $mysqli->query("SELECT * FROM branches ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Add Branch</title>
<link rel="stylesheet" href="style.css"></head><body>
<div class="container">
  <h1>Add Branch</h1>
  <form method="post">
    <label>Branch Name</label>
    <input type="text" name="name" required>
    <button type="submit">Save Branch</button>
  </form>

  <h3>Existing Branches</h3>
  <table class="table">
    <tr><th>ID</th><th>Name</th></tr>
    <?php foreach($branches as $b): ?>
      <tr><td><?=esc($b['id'])?></td><td><?=esc($b['name'])?></td></tr>
    <?php endforeach; ?>
  </table>
  <p><a href="dashboard.php">Back</a></p>
</div></body></html>