<?php  
//dashboard.php  
require_once 'helpers.php';  
require_login();  
$user = current_user();  
?>  
<!doctype html>  
<html>  
<head>  
<meta charset="utf-8">  
<title>Dashboard</title>  

<!-- ICONS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: Arial, sans-serif;
}

/* -------------------- MAIN LAYOUT -------------------- */
body{
    background:#eef3fb;
    display:flex;
    transition:0.3s ease-in-out;
}

/* -------------------- SIDEBAR -------------------- */
.sidebar{
    width:260px;
    background:#004aad;
    height:100vh;
    padding:20px 10px;
    position:fixed;
    left:0;
    top:0;
    color:white;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
    animation:slideIn 0.6s ease;
}

@keyframes slideIn {
    from{ transform:translateX(-100px); opacity:0; }
    to{ transform:translateX(0); opacity:1; }
}

.sidebar h2{
    text-align:center;
    margin-bottom:25px;
    font-size:20px;
    letter-spacing:1px;
}

.sidebar a{
    display:block;
    padding:12px;
    border-radius:8px;
    color:white;
    text-decoration:none;
    margin-bottom:10px;
    font-size:16px;
    transition:0.2s;
}

.sidebar a i{
    width:20px;
}

.sidebar a:hover{
    background:#002f6e;
    transform:scale(1.03);
}

/* -------------------- TOP HEADER -------------------- */
.top-bar{
    margin-left:260px;
    width:100%;
    padding:20px;
    background:white;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.top-title{
    font-size:24px;
    font-weight:bold;
    color:#004aad;
}

/* Theme Button */
#themeToggle{
    background:#222;
    border:none;
    padding:10px 18px;
    border-radius:8px;
    color:white;
    cursor:pointer;
    font-size:16px;
}

/* -------------------- CONTENT -------------------- */
.page{
    margin-left:260px;
    width:100%;
    padding:30px;
}

.panel-box{
    background:white;
    padding:20px;
    margin-bottom:25px;
    border-radius:14px;
    width:100%;
    max-width:500px;
    box-shadow:0 3px 15px rgba(0,0,0,0.18);
}

.panel-box h3{
    text-align:center;
    font-size:20px;
    color:#004aad;
    margin-bottom:10px;
}

.panel-box a button{
    width:100%;
    padding:12px;
    border:none;
    background:#0066ff;
    color:white;
    border-radius:8px;
    margin:8px 0;
    cursor:pointer;
}

.logout-btn{
    padding:12px 18px;
    background:#d63031;
    border:none;
    color:white;
    border-radius:6px;
    cursor:pointer;
    margin-top:20px;
}

/* -------------------- DARK MODE -------------------- */
body.dark{
    background:#1c1c1c;
}

body.dark .sidebar{
    background:#111;
}

body.dark .top-bar{
    background:#222;
    color:white;
}

body.dark .top-title{
    color:#fff;
}

body.dark .panel-box{
    background:#2b2b2b;
    color:white;
}

body.dark a{
    color:white;
}

body.dark #themeToggle{
    background:#f1c40f;
    color:black;
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h2><i class="fa fa-bars"></i> Menu</h2>

    <!-- ⭐ FINAL REPORT FOR ALL ROLES -->
    <a href="final_report.php"><i class="fa fa-file-alt"></i> Final Report</a>

    <?php if($user['role']=='admin'): ?>
        <a href="admin_dashboard.php"><i class="fa fa-chart-pie"></i> Overview</a>
        <a href="add_branch.php"><i class="fa fa-building"></i> Add Branch</a>
        <a href="add_class.php"><i class="fa fa-school"></i> Add Class</a>
        <a href="add_user.php"><i class="fa fa-user-plus"></i> Add User</a>
        <a href="manage_users.php"><i class="fa fa-users-cog"></i> Edit / Delete Users</a>

        <!-- ⭐ STUDENTS SECTION -->
        <a href="students_list.php"><i class="fa fa-user-graduate"></i> View Students</a>
        <a href="timetable_view.php"><i class="fa fa-calendar"></i> View Timetable</a>
        <a href="admin_working_days.php"><i class="fa fa-clock"></i> Working Days</a>
        <a href="admin_attendance_class.php"><i class="fa fa-list"></i> Class Attendance</a>
        <a href="admin_attendance_branch.php"><i class="fa fa-layer-group"></i> Branch Attendance</a>
        <a href="admin_attendance_college.php"><i class="fa fa-chart-pie"></i> College Attendance</a>
    <?php endif; ?>

    <?php if($user['role']=='hod'): ?>
        <a href="hod_view_students.php"><i class="fa fa-users"></i> View Students</a>
        <a href="hod_view_timetable.php"><i class="fa fa-calendar"></i> Timetable</a>
        <a href="hod_holidays.php"><i class="fa fa-umbrella-beach"></i> Holidays</a>
        <a href="hod_working_days.php"><i class="fa fa-clock"></i> Working Days</a>
		<a href="admin_attendance_class.php"><i class="fa fa-list"></i> Class Attendance</a>
        <a href="admin_attendance_branch.php"><i class="fa fa-layer-group"></i> Branch Attendance</a>
    <?php endif; ?>

    <?php if($user['role']=='class'): ?>
        <a href="add_student.php"><i class="fa fa-user-plus"></i> Add Student</a>
        <a href="students_list.php"><i class="fa fa-edit"></i> Edit / Delete Students</a>
        <a href="manage_timetable.php"><i class="fa fa-calendar-plus"></i> Manage Timetable</a>
        <a href="timetable_view.php"><i class="fa fa-calendar-alt"></i> View Timetable</a>
		<a href="admin_attendance_class.php"><i class="fa fa-list"></i> Class Attendance</a>
    <?php endif; ?>

    <?php if($user['role']=='lecturer'): ?>
        <a href="mark_attendance.php"><i class="fa fa-check-circle"></i> Mark Attendance</a>
    <?php endif; ?>

    <a href="logout.php" style="margin-top:20px;"><i class="fa fa-power-off"></i> Logout</a>

</div>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="top-title">Welcome, <?= esc($user['full_name'] ?: $user['username']) ?></div>
    <button id="themeToggle">Dark</button>
</div>

<script>
const btn = document.getElementById('themeToggle');

btn.onclick = ()=>{
    document.body.classList.toggle("dark");
    btn.textContent = document.body.classList.contains("dark") ? "Light" : "Dark";
};
</script>

</body>
</html>