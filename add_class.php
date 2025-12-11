<?php
require_once 'helpers.php';
require_login();
$user = current_user();
if($user['role'] != 'admin'){ flash('Access denied'); header('Location: dashboard.php'); exit; }

$branches = $mysqli->query("SELECT * FROM branches")->fetch_all(MYSQLI_ASSOC);

if($_SERVER['REQUEST_METHOD']=='POST'){
    $branch_id = (int)$_POST['branch_id'];
    $name = $mysqli->real_escape_string($_POST['name']);
    if($name==''){ flash('Class name required'); header('Location:add_class.php'); exit; }
    $stmt = $mysqli->prepare("INSERT INTO classes (branch_id,name) VALUES (?,?)");
    $stmt->bind_param('is',$branch_id,$name);
    if($stmt->execute()){ flash('Class added'); header('Location:add_class.php'); exit; }
    else { flash('DB error: '.$mysqli->error); header('Location:add_class.php'); exit; }
}

$classes = $mysqli->query("SELECT c.*, b.name as branch_name FROM classes c LEFT JOIN branches b ON c.branch_id=b.id ORDER BY c.id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Add Class</title>
<link rel="stylesheet" href="style.css"></head><body>
<div class="container">
  <h1>Add Class</h1>
  <form method="post">
    <label>Branch</label>
    <select name="branch_id" required>
      <?php foreach($branches as $br) echo "<option value=\"{$br['id']}\">".esc($br['name'])."</option>"; ?>
    </select>
    <label>Class Name</label>
    <input type="text" name="name" required>
    <button type="submit">Save Class</button>
  </form>

  <h3>Existing Classes</h3>
  <table class="table">
    <tr><th>ID</th><th>Branch</th><th>Name</th></tr>
    <?php foreach($classes as $c): ?>
      <tr><td><?=esc($c['id'])?></td><td><?=esc($c['branch_name'])?></td><td><?=esc($c['name'])?></td></tr>
    <?php endforeach; ?>
  </table>
  <p><a href="dashboard.php">Back</a></p>
</div></body></html>