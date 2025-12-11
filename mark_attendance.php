<?php
require_once 'helpers.php';
require_login();
$user = current_user();
$mysqli->set_charset("utf8mb4");

$today = date('Y-m-d');
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

$classes = $mysqli->query("SELECT id,name FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$semesters = [1,2,3,4,5,6];

/* ================================================================
   STEP 1 : SELECT SEM + CLASS + DATE + BATCH
================================================================ */
if($step === 1){
    $flash = get_flash();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Mark Attendance - Step 1</title>
<link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
<h1>Mark Attendance — Step 1</h1>

<?php if($flash): ?><div class="notice"><?=esc($flash)?></div><?php endif; ?>

<div class="card">
<form method="get">
<input type="hidden" name="step" value="2">

<label>Semester</label>
<select name="sem" required>
<option value="">-- select semester --</option>
<?php foreach($semesters as $s): ?>
<option value="<?=$s?>">Sem <?=$s?></option>
<?php endforeach; ?>
</select>

<label>Class</label>
<select name="class_id" required>
<option value="">-- select class --</option>
<?php foreach($classes as $c): ?>
<option value="<?=$c['id']?>"><?=esc($c['name'])?></option>
<?php endforeach; ?>
</select>

<label>Date</label>
<input type="date" name="date" value="<?=$today?>" required>

<label>Batch (Academic Year)</label>
<select name="batch" required>
<option value="">-- select year --</option>
<?php foreach(academic_year_options(5,70) as $ay): ?>
<option value="<?=esc($ay)?>"><?=esc($ay)?></option>
<?php endforeach; ?>
</select>

<br><br>
<button type="submit">Next: Select Hour</button>
</form>
</div>

<p><a href="dashboard.php">Back</a></p>
</div>
</body></html>
<?php
exit;
}


/* ================================================================
   STEP 2 : CHECK SUNDAY / HOLIDAY + LOAD TIMETABLE
================================================================ */
if($step === 2){

$sem      = (int)($_GET['sem'] ?? 0);
$class_id = (int)($_GET['class_id'] ?? 0);
$date     = $mysqli->real_escape_string($_GET['date'] ?? $today);
$batch    = $mysqli->real_escape_string($_GET['batch'] ?? '');

if(!$sem || !$class_id || !$batch){
    flash("Please select semester, class and batch.");
    header("Location: mark_attendance.php");
    exit;
}

/* CHECK IF SUNDAY */
$dayNum = date('N', strtotime($date)); 
if($dayNum == 7){
    ?>
    <!doctype html><html><head><meta charset="utf-8"><title>Holiday</title>
    <link rel="stylesheet" href="style.css"></head>
    <body><div class="container"><div class="card">
    <h2>Today is Sunday</h2>
    <p>Attendance cannot be marked on Sunday.</p>
    <p><a href="mark_attendance.php">Back</a></p>
    </div></div></body></html>
    <?php
    exit;
}

/* CHECK HOLIDAY */
$holiday = $mysqli->query("SELECT * FROM holidays WHERE holiday_date='$date' LIMIT 1")->fetch_assoc();
if($holiday){
    ?>
    <!doctype html><html><head><meta charset="utf-8"><title>Holiday</title>
    <link rel="stylesheet" href="style.css"></head>
    <body><div class="container"><div class="card">
    <h2>Holiday</h2>
    <p><strong>Date:</strong> <?=$date?></p>
    <p><strong>Reason:</strong> <?=esc($holiday['reason'])?></p>
    <p><a href="mark_attendance.php">Back</a></p>
    </div></div></body></html>
    <?php
    exit;
}

/* LOAD TIMETABLE */
$day_of_week = (int)date('N', strtotime($date));

$stmt = $mysqli->prepare("
    SELECT t.*, u.full_name AS lecturer_name, c.name AS class_name
    FROM timetable t
    LEFT JOIN users u ON t.lecturer_id = u.id
    LEFT JOIN classes c ON t.class_id = c.id
    WHERE t.class_id = ? AND t.semester = ? AND t.day_of_week = ?
    ORDER BY t.hour_no, t.start_time
");
$stmt->bind_param('iii', $class_id, $sem, $day_of_week);
$stmt->execute();
$tt = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Mark Attendance - Step 2</title>
<link rel="stylesheet" href="style.css"></head><body>
<div class="container">

<h1>Mark Attendance — Step 2</h1>

<div class="card">
<p><strong>Class:</strong> <?=esc($tt[0]['class_name'] ?? '')?>
&nbsp;<strong>Semester:</strong> <?=$sem?>
&nbsp;<strong>Date:</strong> <?=$date?>
&nbsp;<strong>Batch:</strong> <?=$batch?></p>

<?php if(empty($tt)): ?>
    <div class="notice">No timetable entries found. Please add timetable.</div>
    <p><a href="mark_attendance.php">Back</a></p>

<?php else: ?>

<form method="get">
<input type="hidden" name="step" value="3">
<input type="hidden" name="sem" value="<?=$sem?>">
<input type="hidden" name="class_id" value="<?=$class_id?>">
<input type="hidden" name="date" value="<?=$date?>">
<input type="hidden" name="batch" value="<?=$batch?>">

<label>Select Hour</label>
<select name="timetable_id" required>

<?php foreach($tt as $r): ?>

<?php
/* ============================================================
   🔥 NEW FEATURE ADDED → Check if attendance already submitted
============================================================ */
$start = $r['start_time'];
$chk = $mysqli->query("
    SELECT 1 FROM attendance 
    WHERE date='$date'
      AND time='$start'
      AND batch_year='$batch'
      AND student_id IN (SELECT id FROM students WHERE class_id=$class_id)
    LIMIT 1
")->fetch_assoc();

$already = ($chk) ? true : false;
?>
<option value="<?=$r['id']?>" <?=$already?'disabled style="background:#ffcccc"':''?> >
Hour <?=$r['hour_no']?> — <?=esc($r['subject_name'])?>  
<?=substr($r['start_time'],0,5)?> - <?=substr($r['end_time'],0,5)?>  
<?=$already ? " (Already Submitted)" : ""?>
</option>

<?php endforeach; ?>

</select>

<br><br>
<button type="submit">Load Students</button>
</form>

<p><a href="mark_attendance.php?step=1">Back</a></p>

<?php endif; ?>

</div>
</div>
</body></html>
<?php
exit;
}


/* ================================================================
   STEP 3 : MARK PRESENT / ABSENT
================================================================ */
if($step === 3){

$sem      = (int)($_GET['sem'] ?? 0);
$class_id = (int)($_GET['class_id'] ?? 0);
$date     = $mysqli->real_escape_string($_GET['date'] ?? $today);
$timetable_id = (int)($_GET['timetable_id'] ?? 0);
$batch    = $mysqli->real_escape_string($_GET['batch'] ?? '');

if(!$sem || !$class_id || !$timetable_id || !$batch){
    flash("Missing details");
    header("Location: mark_attendance.php"); exit;
}

/* LOAD HOUR DETAILS */
$stmt = $mysqli->prepare("SELECT t.*, c.name AS class_name FROM timetable t
                          LEFT JOIN classes c ON t.class_id=c.id
                          WHERE t.id=? LIMIT 1");
$stmt->bind_param('i',$timetable_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$row){
    flash("Invalid hour");
    header("Location: mark_attendance.php?step=2&sem=$sem&class_id=$class_id&date=$date&batch=$batch");
    exit;
}

/* LOAD STUDENTS */
$stmt = $mysqli->prepare("SELECT id,roll_no,name FROM students
                          WHERE class_id=? AND batch_year=? ORDER BY roll_no");
$stmt->bind_param('is',$class_id,$batch);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Mark Attendance - Step 3</title>
<link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
<h1>Mark Attendance — Step 3</h1>

<div class="card">
<p>
<strong>Class:</strong> <?=$row['class_name']?> <br>
<strong>Semester:</strong> <?=$sem?><br>
<strong>Date:</strong> <?=$date?><br>
<strong>Hour:</strong> <?=$row['hour_no']?><br>
<strong>Time:</strong> <?=substr($row['start_time'],0,5)?> - <?=substr($row['end_time'],0,5)?><br>
<strong>Subject:</strong> <?=$row['subject_name']?><br>
<strong>Batch:</strong> <?=$batch?>
</p>
</div>

<div class="card">
<?php if(empty($students)): ?>
    <div class="notice">No students found.</div>
<?php else: ?>

<form method="post" action="submit_attendance.php">
<input type="hidden" name="class_id" value="<?=$class_id?>">
<input type="hidden" name="date" value="<?=$date?>">
<input type="hidden" name="semester" value="<?=$sem?>">
<input type="hidden" name="hour_no" value="<?=$row['hour_no']?>">
<input type="hidden" name="start_time" value="<?=$row['start_time']?>">
<input type="hidden" name="end_time" value="<?=$row['end_time']?>">
<input type="hidden" name="subject_name" value="<?=$row['subject_name']?>">
<input type="hidden" name="type" value="<?=$row['type']?>">
<input type="hidden" name="batch_year" value="<?=$batch?>">

<table class="table">
<tr><th>#</th><th>Roll</th><th>Name</th><th>Present</th><th>Absent</th></tr>

<?php $i=1; foreach($students as $s): ?>
<tr>
<td><?=$i?></td>
<td><?=$s['roll_no']?></td>
<td><?=$s['name']?></td>
<td>
<input type="hidden" name="student_ids[]" value="<?=$s['id']?>">
<label><input type="radio" name="status_<?=$s['id']?>" value="present" checked> Present</label>
</td>
<td>
<label><input type="radio" name="status_<?=$s['id']?>" value="absent"> Absent</label>
</td>
</tr>
<?php $i++; endforeach; ?>

</table>

<br>
<button type="submit">Submit Attendance</button>

</form>

<?php endif; ?>
</div>

<p><a href="mark_attendance.php?step=2&sem=<?=$sem?>&class_id=<?=$class_id?>&date=<?=$date?>&batch=<?=$batch?>">Back</a></p>

</div>
</body></html>
<?php
exit;
}

?>