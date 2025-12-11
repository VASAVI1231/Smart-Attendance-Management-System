<?php
//manage_timetable.php
require_once 'helpers.php';
require_login();
$user = current_user();
$mysqli->set_charset("utf8mb4");

// Semester list
$semesters = [1,2,3,4,5,6];

// Batch Year list from helpers.php
function get_batch_years(){
    $years = year_list(); // use existing function
    $list = [];
    foreach($years as $y){
        $list[] = $y . "-" . ($y+1);
    }
    return $list;
}

// Selected values
$selected_sem   = isset($_GET['sem']) ? (int)$_GET['sem'] : 0;
$selected_batch = isset($_GET['batch']) ? $_GET['batch'] : "";

// EDIT MODE
$edit = false;
$edit_row = null;

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];

    $stmt = $mysqli->prepare("SELECT * FROM timetable WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($edit_row) {
        $edit = true;
    }
}

// Fetch classes
$classes = $mysqli->query("SELECT id, name FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch lecturers
$lecturers = $mysqli->query("SELECT id, full_name FROM users WHERE role='lecturer' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);

// Days
$days = [
    1 => "Monday", 2 => "Tuesday", 3 => "Wednesday",
    4 => "Thursday", 5 => "Friday", 6 => "Saturday"
];

// ---------------- ADD / UPDATE ----------------
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $sem     = (int)$_POST['semester'];
    $class   = (int)$_POST['class_id'];
    $day     = (int)$_POST['day_of_week'];
    $hour    = (int)$_POST['hour_no'];
    $start   = $_POST['start_time'];
    $end     = $_POST['end_time'];
    $subject = trim($_POST['subject_name']);
    $type    = $_POST['type']; 
    $lect    = (int)$_POST['lecturer_id'];
    $batch   = $_POST['batch_year'];

    if ($id > 0) {

        // UPDATE
        $stmt = $mysqli->prepare("
            UPDATE timetable SET
                class_id=?, 
                day_of_week=?, 
                hour_no=?, 
                start_time=?, 
                end_time=?,
                subject_name=?, 
                type=?, 
                lecturer_id=?, 
                semester=?,
                batch_year=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "iiissssissi",
            $class,
            $day,
            $hour,
            $start,
            $end,
            $subject,
            $type,
            $lect,
            $sem,
            $batch,
            $id
        );

        $stmt->execute();
        $stmt->close();
        flash("Updated Successfully!");

    } else {

        // INSERT
        $stmt = $mysqli->prepare("
            INSERT INTO timetable 
            (class_id, day_of_week, hour_no, start_time, end_time, 
             subject_name, type, lecturer_id, semester, batch_year)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "iiissssiss",
            $class,
            $day,
            $hour,
            $start,
            $end,
            $subject,
            $type,
            $lect,
            $sem,
            $batch
        );

        $stmt->execute();
        $stmt->close();

        flash("Added Successfully!");
    }

    header("Location: manage_timetable.php?sem=$sem&batch=$batch");
    exit;
}


// ---------------- DELETE ----------------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $mysqli->query("DELETE FROM timetable WHERE id=$id");
    flash("Deleted!");
    header("Location: manage_timetable.php?sem=$selected_sem&batch=$selected_batch");
    exit;
}


// ---------------- FETCH ROWS ----------------
$rows = [];

if ($selected_sem && $selected_batch) {
    $stmt = $mysqli->prepare("
        SELECT t.*, c.name AS class_name, u.full_name AS lecturer
        FROM timetable t
        LEFT JOIN classes c ON t.class_id = c.id
        LEFT JOIN users u ON t.lecturer_id = u.id
        WHERE t.semester = ? AND t.batch_year = ?
        ORDER BY class_name, day_of_week, hour_no
    ");
    $stmt->bind_param("is", $selected_sem, $selected_batch);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

?>
<!doctype html>
<html>
<head>
<title>Manage Timetable</title>
<link rel="stylesheet" href="style.css">

<style>
.container{max-width:900px;margin:auto;padding:20px;}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 4px 12px #ddd;margin-bottom:18px;}
.table{width:100%;border-collapse:collapse;margin-top:14px;}
.table th,.table td{border:1px solid #ccc;padding:8px;}
.btn{padding:8px 12px;border:none;border-radius:6px;cursor:pointer;}
.add-btn{background:#0a7cff;color:white;}
.edit-btn{background:#ffa500;color:white;}
.del-btn{background:#ff4d4d;color:white;}
</style>
</head>

<body>
<div class="container">

<h1>Manage Timetable</h1>

<!-- STEP 1: SELECT SEMESTER -->
<div class="card">
<form method="get">
<label><strong>Select Semester</strong></label>
<select name="sem" onchange="this.form.submit()">
    <option value="">-- Select --</option>
    <?php foreach ($semesters as $s): ?>
        <option value="<?=$s?>" <?=$selected_sem==$s?'selected':''?>>Semester <?=$s?></option>
    <?php endforeach; ?>
</select>
</form>
</div>

<?php if (!$selected_sem): ?>
<p class="card">Please select semester first.</p>
<?php exit; endif; ?>


<!-- STEP 2: SELECT BATCH -->
<div class="card">
<form method="get">
<input type="hidden" name="sem" value="<?=$selected_sem?>">
<label><strong>Select Batch Year</strong></label>

<select name="batch" onchange="this.form.submit()">
    <option value="">-- Select Batch --</option>
    <?php foreach(get_batch_years() as $b): ?>
        <option value="<?=$b?>" <?=$selected_batch==$b?'selected':''?>><?=$b?></option>
    <?php endforeach; ?>
</select>

</form>
</div>

<?php if (!$selected_batch): ?>
<p class="card">Please select batch year.</p>
<?php exit; endif; ?>


<!-- STEP 3: ADD / EDIT FORM -->
<div class="card">
<h2><?=$edit ? "Edit Entry" : "Add Entry"?> (Sem <?=$selected_sem?> | Batch <?=$selected_batch?>)</h2>

<form method="post">

<input type="hidden" name="semester" value="<?=$selected_sem?>">
<input type="hidden" name="batch_year" value="<?=$selected_batch?>">

<?php if($edit): ?>
<input type="hidden" name="id" value="<?=$edit_row['id']?>">
<?php endif; ?>

<label>Class</label>
<select name="class_id" required>
<option value="">Select</option>
<?php foreach($classes as $c): ?>
<option value="<?=$c['id']?>" <?=($edit && $edit_row['class_id']==$c['id'])?'selected':''?>><?=$c['name']?></option>
<?php endforeach; ?>
</select>

<label>Day</label>
<select name="day_of_week" required>
<?php foreach($days as $k=>$d): ?>
<option value="<?=$k?>" <?=($edit && $edit_row['day_of_week']==$k)?'selected':''?>><?=$d?></option>
<?php endforeach; ?>
</select>

<label>Hour No</label>
<select name="hour_no" required>
<?php for($i=1;$i<=12;$i++): ?>
<option value="<?=$i?>" <?=($edit && $edit_row['hour_no']==$i)?'selected':''?>>Hour <?=$i?></option>
<?php endfor; ?>
</select>

<label>Start Time</label>
<input type="time" name="start_time" required value="<?=$edit?$edit_row['start_time']:''?>">

<label>End Time</label>
<input type="time" name="end_time" required value="<?=$edit?$edit_row['end_time']:''?>">

<label>Subject</label>
<input type="text" name="subject_name" required value="<?=$edit?$edit_row['subject_name']:''?>">

<label>Type</label>
<select name="type" required>
    <option value="class" <?=($edit && $edit_row['type']=='class')?'selected':''?>>Class</option>
    <option value="lab" <?=($edit && $edit_row['type']=='lab')?'selected':''?>>Lab</option>
</select>

<label>Lecturer</label>
<select name="lecturer_id" required>
<option value="">Select lecturer</option>
<?php foreach($lecturers as $l): ?>
<option value="<?=$l['id']?>" <?=($edit && $edit_row['lecturer_id']==$l['id'])?'selected':''?>><?=$l['full_name']?></option>
<?php endforeach; ?>
</select>

<br><br>
<button class="btn <?=$edit?'edit-btn':'add-btn'?>"><?=$edit ? "Update" : "Add"?></button>

</form>
</div>


<!-- TABLE -->
<div class="card">
<h2>Existing Entries – Semester <?=$selected_sem?> | Batch <?=$selected_batch?></h2>

<?php if(!$rows): ?>
<p>No data found.</p>
<?php else: ?>

<table class="table">
<tr>
<th>Class</th>
<th>Day</th>
<th>Hour</th>
<th>Subject</th>
<th>Time</th>
<th>Type</th>
<th>Action</th>
</tr>

<?php foreach($rows as $r): ?>
<tr>
<td><?=$r['class_name']?></td>
<td><?=$days[$r['day_of_week']]?></td>
<td><?=$r['hour_no']?></td>
<td><?=$r['subject_name']?></td>
<td><?=substr($r['start_time'],0,5)?> - <?=substr($r['end_time'],0,5)?></td>
<td><?=$r['type']?></td>
<td>
 <a href="manage_timetable.php?edit=<?=$r['id']?>&sem=<?=$selected_sem?>&batch=<?=$selected_batch?>">
    <button class="btn edit-btn">Edit</button>
 </a>
 <a href="manage_timetable.php?delete=<?=$r['id']?>&sem=<?=$selected_sem?>&batch=<?=$selected_batch?>" onclick="return confirm('Delete this entry?')">
    <button class="btn del-btn">Delete</button>
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