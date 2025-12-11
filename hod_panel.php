<?php
//hod_panel.php
require_once 'helpers.php';
require_login();
$user = current_user();
if($user['role'] != 'hod' && $user['role'] != 'admin'){ flash('Access denied'); header('Location: dashboard.php'); exit; }

$branches = $mysqli->query("SELECT * FROM branches ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$classes = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$flash = get_flash();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>HOD Panel</title>
<link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <div class="header">
    <h1>HOD Dashboard</h1>
    <div class="topbar"><a href="dashboard.php">Back</a> <a href="logout.php">Logout</a></div>
  </div>

  <?php if($flash): ?><div class="notice"><?=esc($flash)?></div><?php endif; ?>

  <div class="card">
    <h3>Reports & Actions</h3>
    <a href="hod_report.php"><button>Final Attendance (select months)</button></a>
    <a href="hod_edit_attendance.php"><button class="secondary">Find & Edit Attendance Record</button></a>
    <p style="margin-top:10px;color:#666">You can also use Manage Timetable / Holidays from Dashboard.</p>
  </div>

  <div class="card">
    <h3>Quick Links</h3>
    <a href="add_student.php"><button>Add / Edit Students</button></a>
    <a href="manage_timetable.php"><button>Manage Time Table</button></a>
    <a href="hod_holidays.php"><button>Holidays</button></a>
  </div>
</div>
</body>
</html>