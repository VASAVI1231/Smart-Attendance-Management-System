<?php
require_once 'helpers.php';
require_login();
$user = current_user();

if(!in_array($user['role'], ['admin','class','hod'])){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

// Load classes and semester list
$classes = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$semesters = [1,2,3,4,5,6];

// Read filters
$selected_batch = $_GET['batch'] ?? '';
$selected_class = $_GET['class_id'] ?? '';
$selected_sem   = $_GET['semester'] ?? '';

$filters_applied = (!empty($selected_batch) || !empty($selected_class) || !empty($selected_sem));

$students = [];

if($filters_applied){
    // Build filter conditions
    $where = [];

    if(!empty($selected_batch)){
        $batch = $mysqli->real_escape_string($selected_batch);
        $where[] = "s.batch_year='$batch'";
    }

    if(!empty($selected_class)){
        $cl = (int)$selected_class;
        $where[] = "s.class_id=$cl";
    }

    if(!empty($selected_sem)){
        $sem = (int)$selected_sem;
        $where[] = "s.semester=$sem";
    }

    $where_sql = "WHERE ".implode(" AND ", $where);

    // Fetch students (ONLY if filters selected)
    $students = $mysqli->query("
        SELECT s.*, 
               c.name AS class_name, 
               b.name AS branch_name
        FROM students s
        LEFT JOIN classes c ON s.class_id=c.id
        LEFT JOIN branches b ON s.branch_id=b.id
        $where_sql
        ORDER BY s.roll_no
    ")->fetch_all(MYSQLI_ASSOC);
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Students List</title>
<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">

<h1>Students List</h1>

<div class="card">
<form method="get">

    <!-- Batch -->
    <label><strong>Select Batch</strong></label>
    <select name="batch">
        <option value="">-- Any --</option>
        <?php foreach(year_list() as $y): ?>
            <?php $batch = $y . "-" . ($y+1); ?>
            <option value="<?=esc($batch)?>" <?=($batch==$selected_batch?'selected':'')?>>
               <?=esc($batch)?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Class -->
    <label><strong>Select Class</strong></label>
    <select name="class_id">
        <option value="">-- Any --</option>
        <?php foreach($classes as $c): ?>
        <option value="<?=$c['id']?>" <?=($c['id']==$selected_class?'selected':'')?>>
            <?=esc($c['name'])?>
        </option>
        <?php endforeach; ?>
    </select>

    <!-- Semester -->
    <label><strong>Select Semester</strong></label>
    <select name="semester">
        <option value="">-- Any --</option>
        <?php foreach($semesters as $s): ?>
        <option value="<?=$s?>" <?=($s==$selected_sem?'selected':'')?>>
            Sem <?=$s?>
        </option>
        <?php endforeach; ?>
    </select>

    <br><br>
    <button type="submit">Search</button>

</form>
</div>

<!-- Students Table -->
<div class="card">
<?php if(!$filters_applied): ?>
    <p><em>Please select Batch / Class / Sem to view students.</em></p>

<?php elseif(empty($students)): ?>
    <p><strong>No students found for selected filters.</strong></p>

<?php else: ?>
<table class="table">
<tr>
    <th>Roll</th>
    <th>Name</th>
    <th>Class</th>
    <th>Branch</th>
    <th>Semester</th>
    <th>Batch</th>
    <th>Phone</th>
    <th>Action</th>
</tr>

<?php foreach($students as $s): ?>
<tr>
    <td><?=esc($s['roll_no'])?></td>
    <td><?=esc($s['name'])?></td>
    <td><?=esc($s['class_name'])?></td>
    <td><?=esc($s['branch_name'])?></td>
    <td><?=esc($s['semester'])?></td>
    <td><?=esc($s['batch_year'])?></td>
    <td><?=esc($s['phone'])?></td>
    <td>
        <a href="add_student.php?id=<?=$s['id']?>"><button>Edit</button></a>
        <a href="delete_student.php?id=<?=$s['id']?>" onclick="return confirm('Delete <?=$s['name']?>?')">
            <button class="danger">Delete</button>
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>
<?php endif; ?>
</div>

<p><a href="dashboard.php">Back</a></p>

</div>
</body>
</html>