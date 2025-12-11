<?php
require_once 'helpers.php';
require_login();
$user = current_user();

if(!in_array($user['role'], ['admin','class'])){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$branches = $mysqli->query("SELECT * FROM branches ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$classes  = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$edit = false;
$student = null;

if(isset($_GET['id'])){
    $edit = true;
    $id = (int)$_GET['id'];
    $student = $mysqli->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();
    if(!$student){
        flash("Student not found");
        header("Location: students_list.php");
        exit;
    }
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $roll_no = $_POST['roll_no'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $parent_email = $_POST['parent_email'];
    $phone = $_POST['phone'];
    $branch_id = $_POST['branch_id'];
    $class_id = $_POST['class_id'];
    $hall_ticket = $_POST['hall_ticket'];

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
                hall_ticket='$hall_ticket'
            WHERE id=".$student['id']
        );
        flash("Student updated successfully");
    } else {
        $mysqli->query("
            INSERT INTO students
                (roll_no,name,email,parent_email,phone,branch_id,class_id,hall_ticket)
            VALUES
                ('$roll_no','$name','$email','$parent_email','$phone','$branch_id','$class_id','$hall_ticket')
        ");
        flash("Student added successfully");
    }

    header("Location: students_list.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= $edit ? "Edit" : "Add" ?> Student</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

<h1><?= $edit ? "Edit" : "Add" ?> Student</h1>

<form method="post">

<label>Roll No</label>
<input type="text" name="roll_no" value="<?=$student['roll_no'] ?? ''?>" required>

<label>Student Name</label>
<input type="text" name="name" value="<?=$student['name'] ?? ''?>" required>

<label>Student Email</label>
<input type="email" name="email" value="<?=$student['email'] ?? ''?>">

<label>Parent Email</label>
<input type="email" name="parent_email" value="<?=$student['parent_email'] ?? ''?>">

<label>Phone Number</label>
<input type="text" name="phone" value="<?=$student['phone'] ?? ''?>">

<label>Branch</label>
<select name="branch_id">
<?php foreach($branches as $b): ?>
<option value="<?=$b['id']?>" <?=isset($student['branch_id']) && $student['branch_id']==$b['id']?'selected':''?>>
  <?=$b['name']?>
</option>
<?php endforeach; ?>
//delete_student.php
</select>

<label>Class</label>
<select name="class_id">
<?php foreach($classes as $c): ?>
<option value="<?=$c['id']?>" <?=isset($student['class_id']) && $student['class_id']==$c['id']?'selected':''?>>
  <?=$c['name']?>
</option>
<?php endforeach; ?>
</select>

<label>Hall Ticket</label>
<input type="text" name="hall_ticket" value="<?=$student['hall_ticket'] ?? ''?>">

<button type="submit"><?= $edit ? "Update" : "Add" ?> Student</button>

</form>

<p><a href="students_list.php">Back</a></p>

</div>
</body>
</html>