<?php  
// hod_view_students.php
require_once 'helpers.php';  
require_login();  
$user = current_user();  

if($user['role']!='hod'){  
    flash("Access denied");  
    header("Location: dashboard.php");  
    exit;  
}  

// GET FILTERS
$selected_sem   = isset($_GET['sem']) ? (int)$_GET['sem'] : 0;  
$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;  
$selected_batch = isset($_GET['batch_year']) ? $_GET['batch_year'] : "";  

// DATA LISTS
$semesters = [1,2,3,4,5,6];  
$classes = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);  

$students = [];  

// FETCH STUDENTS ONLY IF ALL FILTERS SELECTED
if($selected_sem && $selected_class && $selected_batch){  

    $stmt = $mysqli->prepare("
        SELECT s.*, b.name AS branch_name, c.name AS class_name
        FROM students s
        LEFT JOIN branches b ON s.branch_id=b.id
        LEFT JOIN classes c ON s.class_id=c.id
        WHERE s.semester = ?
          AND s.class_id = ?
          AND s.batch_year = ?
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
<title>Students List</title>  
<link rel="stylesheet" href="style.css">  

<style>  
.container{max-width:900px;margin:auto;padding:20px;}  
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 4px 12px #ccc;margin-bottom:20px;}  
.table{width:100%;border-collapse:collapse;margin-top:12px;}  
.table th,.table td{border:1px solid #ccc;padding:8px;text-align:left;}  
</style>  

</head>  
<body>  

<div class="container">  
<h1>Students List (HOD)</h1>  

<!-- FILTER CARD -->  
<div class="card">  
<form method="get">

<!-- Semester -->
<label><strong>Select Semester:</strong></label><br>
<select name="sem">
    <option value="">-- Select Semester --</option>
    <?php foreach($semesters as $s): ?>  
        <option value="<?=$s?>" <?=$selected_sem==$s?'selected':''?>>Semester <?=$s?></option>  
    <?php endforeach; ?>  
</select><br><br>

<!-- Class -->
<label><strong>Select Class:</strong></label><br>
<select name="class_id">
    <option value="">-- Select Class --</option>
    <?php foreach($classes as $c): ?>
        <option value="<?=$c['id']?>" <?=$selected_class==$c['id']?'selected':''?>><?=$c['name']?></option>
    <?php endforeach; ?>
</select><br><br>

<!-- Batch -->
<label><strong>Select Batch:</strong></label><br>
<select name="batch_year">
    <option value="">-- Select Batch --</option>
    <?php foreach(year_list() as $y): 
        $batch = $y . "-" . ($y+1);
    ?>
        <option value="<?=$batch?>" <?=$selected_batch==$batch?'selected':''?>><?=$batch?></option>
    <?php endforeach; ?>
</select><br><br>

<button type="submit">Search</button>

</form>  
</div>  

<?php if(!$selected_sem || !$selected_class || !$selected_batch): ?>  
    <div class="card">Please select Semester, Class and Batch to view students.</div>  
<?php else: ?>  

<div class="card">  
<h2>Students – Semester <?=$selected_sem?></h2>  

<?php if(!$students): ?>  
    <p>No students found for this selection.</p>  
<?php else: ?>  

<table class="table">  
<tr>  
    <th>Roll No</th>  
    <th>Name</th>  
    <th>Class</th>  
    <th>Branch</th>  
    <th>Batch</th>
</tr>  

<?php foreach($students as $s): ?>  
<tr>  
    <td><?=esc($s['roll_no'])?></td>  
    <td><?=esc($s['name'])?></td>  
    <td><?=esc($s['class_name'])?></td>  
    <td><?=esc($s['branch_name'])?></td>  
    <td><?=esc($s['batch_year'])?></td>
</tr>  
<?php endforeach; ?>  

</table>  

<?php endif; ?>  
</div>  

<?php endif; ?>  

<p><a href="dashboard.php">Back</a></p>  

</div>  

</body>  
</html>