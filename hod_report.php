<?php
require_once 'helpers.php';
require_login();
$user = current_user();

if($user['role'] != 'hod'){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$classes = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$semesters = [1,2,3,4,5,6];

$selected_class = $_POST['class_id'] ?? '';
$selected_sem   = $_POST['semester'] ?? '';
$selected_batch = $_POST['batch'] ?? '';

$students = [];
$attendance_data = [];

if(!empty($_POST['submit'])){
    
    $class_id = (int)$selected_class;
    $sem = (int)$selected_sem;
    $batch = $mysqli->real_escape_string($selected_batch);

    $batch_cond = $batch ? " AND batch_year='$batch'" : "";

    // Fetch students of selected class + batch
    $students = $mysqli->query("
        SELECT id, roll_no, name 
        FROM students 
        WHERE class_id=$class_id 
        $batch_cond
        ORDER BY roll_no
    ")->fetch_all(MYSQLI_ASSOC);

    // For each student → count present + absent
    foreach($students as $st){

        $sid = (int)$st['id'];

        // Present count
        $r1 = $mysqli->query("
            SELECT COUNT(*) AS c 
            FROM attendance 
            WHERE student_id=$sid 
              AND semester=$sem 
              $batch_cond
              AND status='present'
        ")->fetch_assoc();
        $present = $r1['c'] ?? 0;

        // Total classes for student
        $r2 = $mysqli->query("
            SELECT COUNT(*) AS c 
            FROM attendance 
            WHERE student_id=$sid 
              AND semester=$sem 
              $batch_cond
        ")->fetch_assoc();
        $total = $r2['c'] ?? 0;

        $percent = ($total > 0) ? round(($present / $total) * 100, 2) : 0;

        $attendance_data[] = [
            'roll'     => $st['roll_no'],
            'name'     => $st['name'],
            'present'  => $present,
            'total'    => $total,
            'percent'  => $percent
        ];
    }
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>HOD Report</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

<h1>HOD Final Attendance Report</h1>

<div class="card">
<form method="post">

    <label>Select Class</label>
    <select name="class_id" required>
        <option value="">-- Select --</option>
        <?php foreach($classes as $c): ?>
            <option value="<?=$c['id']?>" <?=($c['id']==$selected_class?'selected':'')?>>
                <?=esc($c['name'])?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Select Semester</label>
    <select name="semester" required>
        <option value="">-- Select --</option>
        <?php foreach($semesters as $s): ?>
            <option value="<?=$s?>" <?=($s==$selected_sem?'selected':'')?>>
                Sem <?=$s?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Select Batch (Academic Year)</label>
    <select name="batch" required>
        <option value="">-- Select --</option>
        <?php foreach(academic_year_options(5,1) as $ay): ?>
            <option value="<?=esc($ay)?>" <?=($ay==$selected_batch?'selected':'')?>>
                <?=esc($ay)?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>
    <button name="submit" type="submit">Generate Report</button>

</form>
</div>

<?php if(!empty($attendance_data)): ?>
<div class="card">
<h2>Report for Class: <?=esc($selected_class)?> | Sem: <?=$selected_sem?> | Batch: <?=esc($selected_batch)?></h2>

<table class="table">
<tr>
    <th>Roll</th>
    <th>Name</th>
    <th>Present</th>
    <th>Total</th>
    <th>%</th>
</tr>

<?php foreach($attendance_data as $r): ?>
<tr>
    <td><?=esc($r['roll'])?></td>
    <td><?=esc($r['name'])?></td>
    <td><?=esc($r['present'])?></td>
    <td><?=esc($r['total'])?></td>
    <td><?=esc($r['percent'])?>%</td>
</tr>
<?php endforeach; ?>

</table>

</div>
<?php endif; ?>

<p><a href="dashboard.php">Back</a></p>

</div>
</body>
</html>