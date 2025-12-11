<?php
require_once 'helpers.php';
require_login();
$user = current_user();
$mysqli->set_charset("utf8mb4");

// Fetch branches
$branches = $mysqli->query("SELECT id,name FROM branches ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Branch Wise Attendance</title>
<link rel="stylesheet" href="style.css">

<style>
/* ---- Small LEFT-side back button inside the card ---- */
.back-small{
    margin-top: 10px;        /* View Attendance button kindha */
    width: 70px;             /* CHINNAGA */
    padding: 6px 0;
    font-size: 12px;
    
    background:#333;      /* dark navy */
    color:white;
    border:none;
    border-radius:6px;

    cursor:pointer;
}
.back-small:hover{
    background:#000;
}
</style>

</head>

<body>

<div class="container">

<h2 style="text-align:center;color:#004aad;margin-bottom:15px;">
    Branch Wise Attendance
</h2>

<div class="card">
<form method="get" action="admin_attendance_branch_view.php">

    <label>Branch</label>
    <select name="branch_id" required>
        <option value="">-- Select Branch --</option>
        <?php foreach($branches as $b): ?>
            <option value="<?=$b['id']?>"><?=$b['name']?></option>
        <?php endforeach; ?>
    </select>

    <label>Semester</label>
    <select name="semester" required>
        <option value="">-- Select --</option>
        <?php for($i=1;$i<=6;$i++): ?>
            <option value="<?=$i?>">Semester <?=$i?></option>
        <?php endfor; ?>
    </select>

    <label>Batch</label>
    <select name="batch" required>
        <option value="">-- Batch --</option>
        <?php foreach(year_list() as $y): $b=$y."-".($y+1); ?>
        <option value="<?=$b?>"><?=$b?></option>
        <?php endforeach; ?>
    </select>

    <label>From Date</label>
    <input type="date" name="from_date" required>

    <label>To Date</label>
    <input type="date" name="to_date" required>

    <button type="submit">View Attendance</button>
	
</form>
</div>
<!-- ⭐ Small Bottom-Left Back Button -->
	<button class="back-small" onclick="window.location='dashboard.php'">Back</button>
</div>

</body>
</html>