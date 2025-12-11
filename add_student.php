<?php
require_once 'helpers.php';
require_login();
$user = current_user();

$branches = $mysqli->query("SELECT * FROM branches ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$classes  = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$edit = false;
$student = null;

if(isset($_GET['id'])){
    $edit = true;
    $id = (int)$_GET['id'];
    $student = $mysqli->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $roll_no = $mysqli->real_escape_string($_POST['roll_no']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $parent_email = $mysqli->real_escape_string($_POST['parent_email']);
    $phone = $mysqli->real_escape_string($_POST['phone']);
    $branch_id = (int)$_POST['branch_id'];
    $class_id = (int)$_POST['class_id'];
    $semester = (int)$_POST['semester'];
    $hall_ticket = $mysqli->real_escape_string($_POST['hall_ticket']);
    $batch_year = $mysqli->real_escape_string($_POST['batch_year'] ?? '');

    if($edit){
        $mysqli->query("
            UPDATE students SET
                roll_no='$roll_no',
                name='$name',
                email='$email',
                parent_email='$parent_email',
                phone='$phone',
                branch_id='$branch_id',
                class_id='$class_id',
                semester='$semester',
                hall_ticket='$hall_ticket',
                batch_year='$batch_year'
            WHERE id=".$student['id']
        );
        flash("Student Updated");
    } else {
        $mysqli->query("
            INSERT INTO students
            (roll_no,name,email,parent_email,phone,branch_id,class_id,semester,hall_ticket,batch_year)
            VALUES
            ('$roll_no','$name','$email','$parent_email','$phone','$branch_id','$class_id','$semester','$hall_ticket','$batch_year')
        ");
        flash("Student Added");
    }

    header("Location: students_list.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<title><?= $edit ? "Edit Student" : "Add Student" ?></title>
<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">

<h1><?= $edit ? "Edit Student" : "Add Student" ?></h1>

<form method="post">

<label>Roll No</label>
<input type="text" name="roll_no" required value="<?=esc($student['roll_no'] ?? '')?>">

<label>Student Name</label>
<input type="text" name="name" required value="<?=esc($student['name'] ?? '')?>">

<label>Email</label>
<input type="email" name="email" value="<?=esc($student['email'] ?? '')?>">

<label>Parent Email</label>
<input type="email" name="parent_email" value="<?=esc($student['parent_email'] ?? '')?>">

<label>Phone</label>
<input type="text" name="phone" value="<?=esc($student['phone'] ?? '')?>">

<label>Branch</label>
<select name="branch_id">
<?php foreach($branches as $b): ?>
<option value="<?=$b['id']?>" <?=isset($student['branch_id']) && $student['branch_id']==$b['id']?'selected':''?>>
<?=esc($b['name'])?>
</option>
<?php endforeach; ?>
</select>

<label>Class</label>
<select name="class_id">
<?php foreach($classes as $c): ?>
<option value="<?=$c['id']?>" <?=isset($student['class_id']) && $student['class_id']==$c['id']?'selected':''?>>
<?=esc($c['name'])?>
</option>
<?php endforeach; ?>
</select>

<label>Semester</label>
<select name="semester" required>
<option value="">Select</option>
<?php for($i=1;$i<=6;$i++): ?>
<option value="<?=$i?>" <?=isset($student['semester']) && $student['semester']==$i?'selected':''?>><?=$i?></option>
<?php endfor; ?>
</select>

<label>Batch (Academic Year)</label>
<select name="batch_year" required>
  <option value="">-- select year --</option>

  <?php foreach(year_list() as $y): ?>
      <?php $batch = $y . "-" . ($y+1); ?>
      <option value="<?=esc($batch)?>"
      <?=isset($student['batch_year']) && $student['batch_year']==$batch ? 'selected' : ''?>>
        <?=esc($batch)?>
      </option>
  <?php endforeach; ?>

</select>

<label>Hall Ticket</label>
<input type="text" name="hall_ticket" value="<?=esc($student['hall_ticket'] ?? '')?>">

<br><br>
<button type="submit"><?= $edit ? "Update" : "Add" ?></button>

</form>

<p><a href="dashboard.php">Back</a></p>

</div>
</body>
</html>