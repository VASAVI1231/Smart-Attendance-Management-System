<?php
// add_student_process.php
require_once 'helpers.php';
if(!is_logged_in()) { header('Location: index.php'); exit; }
$user = current_user();
if(!in_array($user['role'], ['admin','hod','class'])) { die('Access denied'); }

$roll = $mysqli->real_escape_string($_POST['roll_no']);
$name = $mysqli->real_escape_string($_POST['name']);
$email = $mysqli->real_escape_string($_POST['email']);
$parent_email = $mysqli->real_escape_string($_POST['parent_email']);
$phone = $mysqli->real_escape_string($_POST['phone']);
$branch_id = (int)$_POST['branch_id'];
$class_id = (int)$_POST['class_id'];
$semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 0;
$hall = $mysqli->real_escape_string($_POST['hall_ticket'] ?? '');
$batch = $mysqli->real_escape_string($_POST['batch_year'] ?? '');
$pwd = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO students (roll_no,name,email,parent_email,phone,branch_id,class_id,semester,hall_ticket,batch_year) VALUES (?,?,?,?,?,?,?,?,?,?)");
$stmt->bind_param('ssssiiisss',$roll,$name,$email,$parent_email,$phone,$branch_id,$class_id,$semester,$hall,$batch);
if($stmt->execute()){
    echo "Student added. <a href='dashboard.php'>Back</a>";
} else {
    echo "Error: " . $mysqli->error;
}
?>