<?php
//login_process.php
require_once 'helpers.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ header('Location:index.php'); exit; }
$username = $mysqli->real_escape_string($_POST['username']);
$password = $_POST['password'];
$role = $mysqli->real_escape_string($_POST['role']);

$q = $mysqli->query("SELECT * FROM users WHERE username='$username' AND role='$role' LIMIT 1");
if(!$q || $q->num_rows==0){
    flash('Invalid username or role.'); header('Location:index.php'); exit;
}
$user = $q->fetch_assoc();
$dbpass = $user['password'];
$ok = false;
if(password_verify($password, $dbpass)) $ok = true;
if(!$ok && $password === $dbpass) $ok = true; // support unhashed placeholder

if($ok){
    $_SESSION['user_id'] = $user['id'];
    flash('Welcome, '.$user['full_name']);
    header('Location: dashboard.php'); exit;
}else{
    flash('Invalid password'); header('Location:index.php'); exit;
}
?>