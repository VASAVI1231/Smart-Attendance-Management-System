<?php
//delete_timetable.php
require_once 'helpers.php';
require_login();
$user = current_user();

if(!in_array($user['role'], ['admin','class'])){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if($id){
    $mysqli->query("DELETE FROM timetable WHERE id=$id");
    flash("Time table entry deleted");
}

header("Location: manage_timetable.php");
exit;