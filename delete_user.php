<?php
//delete_user.php
require_once 'helpers.php';
require_login();
$user = current_user();

if($user['role']!='admin'){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

// Safety: Cannot delete admin account
$check = $mysqli->query("SELECT role FROM users WHERE id=$id")->fetch_assoc();
if(!$check){
    flash("User not found");
    header("Location: manage_users.php");
    exit;
}

if($check['role']=='admin'){
    flash("Admin account cannot be deleted");
    header("Location: manage_users.php");
    exit;
}

// Delete user
$mysqli->query("DELETE FROM users WHERE id=$id");

flash("User deleted successfully");
header("Location: manage_users.php");
exit;
?>