<?php
// submit_attendance.php
require_once 'helpers.php';
require_login();
$user = current_user();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: mark_attendance.php');
    exit;
}

$class_id     = (int)$mysqli->real_escape_string($_POST['class_id'] ?? 0);
$date         = $mysqli->real_escape_string($_POST['date'] ?? '');
$semester     = (int)($mysqli->real_escape_string($_POST['semester'] ?? 0));
$hour_no      = (int)($mysqli->real_escape_string($_POST['hour_no'] ?? 0));
$start_time   = $mysqli->real_escape_string($_POST['start_time'] ?? '');
$end_time     = $mysqli->real_escape_string($_POST['end_time'] ?? '');
$subject_name = $mysqli->real_escape_string($_POST['subject_name'] ?? '');
$type         = $mysqli->real_escape_string($_POST['type'] ?? '');
$batch_year   = $mysqli->real_escape_string($_POST['batch_year'] ?? '');
$student_ids  = $_POST['student_ids'] ?? [];
$time_now     = date('H:i:s');

// --------------------------------------
// ⭐ 1. CHECK IF SELECTED DATE IS SUNDAY
// --------------------------------------
$dayName = date('l', strtotime($date)); // Sunday, Monday, etc

if($dayName === 'Sunday'){
    flash("Today is Sunday – Attendance cannot be taken.");
    header("Location: mark_attendance.php");
    exit;
}

// --------------------------------------
// ⭐ 2. CHECK IF DATE IS A HOLIDAY
// --------------------------------------
$holiday = $mysqli->query("SELECT * FROM holidays WHERE holiday_date='$date' LIMIT 1")->fetch_assoc();

if($holiday){
    flash("Holiday: ".$holiday['reason']." – Attendance cannot be taken.");
    header("Location: mark_attendance.php");
    exit;
}

// --------------------------------------
// ⭐ 3. PROCEED WITH NORMAL ATTENDANCE
// --------------------------------------
foreach($student_ids as $sid){

    $sid = (int)$sid;
    $field = 'status_'.$sid;
    $status = ($_POST[$field] ?? 'absent');

    if($status !== 'present'){
        $status = 'absent';
    }

    // Notes field
    $notes = "Sem: $semester | Hour: $hour_no | Subject: $subject_name | Time: $start_time-$end_time | Batch: $batch_year";

    // Avoid duplicate entries
    $mysqli->query("DELETE FROM attendance
                    WHERE student_id=$sid
                      AND date='$date'
                      AND time='$start_time'
                      AND batch_year='".$mysqli->real_escape_string($batch_year)."'");

    // Insert new attendance row
    $stmt = $mysqli->prepare("
        INSERT INTO attendance
        (student_id, date, time, status, type, recorded_by, notes, semester, batch_year)
        VALUES (?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        'issssisss',
        $sid,
        $date,
        $start_time,
        $status,
        $type,
        $user['id'],
        $notes,
        $semester,
        $batch_year
    );

    $stmt->execute();
    $stmt->close();
}

flash("Attendance saved for $date (Hour $hour_no - $subject_name) [Batch: $batch_year]");
header('Location: mark_attendance.php');
exit;

?>