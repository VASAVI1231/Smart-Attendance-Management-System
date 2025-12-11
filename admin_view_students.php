<?php
require_once 'helpers.php';
require_login();
$user = current_user();

// Only admin access
if($user['role'] != 'admin'){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$selected_sem = isset($_GET['sem']) ? (int)$_GET['sem'] : 0;
$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$selected_batch = $_GET['batch'] ?? "";

$semesters = [1,2,3,4,5,6];
$classes = $mysqli->query("SELECT id,name FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$students = [];

if($selected_sem && $selected_class && $selected_batch){
    $stmt = $mysqli->prepare("
        SELECT s.*, c.name AS class_name 
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        WHERE s.semester = ? AND s.class_id = ? AND s.batch_year = ?
        ORDER BY s.roll_no
    ");
    $stmt->bind_param("iis", $selected_sem, $selected_class, $selected_batch);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin – View Students</title>
<link rel="stylesheet" href="style.css">

<style>
.container{max-width:900px;margin:auto;padding:20px;}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 4px 12px #ccc;margin-bottom:20px;}
.table{width:100%;border-collapse:collapse;margin-top:12px;}
.table th,.table td{border:1px solid #ccc;padding:8px;}
</style>

</head>
<body>

<div class="container">
<h1>View Students (Admin)</h1>

<div class="card">
<form method="get">

<label>Select Semester:</label>
<select name="sem" required>
  <option value="">-- Select --</option>
  <?php foreach($semesters as $s): ?>
    <option value="<?=$s?>" <?=$selected_sem==$s?'selected':''?>>Semester <?=$s?></option>
  <?php endforeach; ?>
</select>

<label>Select Class:</label>
<select name="class_id" required>
  <option value="">-- Select --</option>
  <?php foreach($classes as $c): ?>
    <option value="<?=$c['id']?>" <?=$selected_class==$c['id']?'selected':''?>><?=$c['name']?></option>
  <?php endforeach; ?>
</select>

<label>Select Batch:</label>
<select name="batch" required>
<option value="">-- Batch --</option>
<?php foreach(year_list() as $y): 
      $b = $y."-".($y+1);
?>
<option value="<?=$b?>" <?=$selected_batch==$b?'selected':''?>><?=$b?></option>
<?php endforeach; ?>
</select>

<br><br>
<button type="submit">Load Students</button>

</form>
</div>

<?php if($students): ?>
<div class="card">
<table class="table">
<tr>
<th>Roll No</th>
<th>Name</th>
<th>Class</th>
<th>Batch</th>
<th>Semester</th>
</tr>

<?php foreach($students as $s): ?>
<tr>
<td><?=esc($s['roll_no'])?></td>
<td><?=esc($s['name'])?></td>
<td><?=esc($s['class_name'])?></td>
<td><?=esc($s['batch_year'])?></td>
<td><?=esc($s['semester'])?></td>
</tr>
<?php endforeach; ?>

</table>
</div>
<?php endif; ?>

<a href="dashboard.php">Back</a>

</div>
</body>
</html>