<?php  
require_once 'helpers.php';  
require_login();  
$user = current_user();  
$mysqli->set_charset("utf8mb4");  

// Fetch classes  
$classes = $mysqli->query("SELECT id,name FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Class Wise Attendance</title>

<style>
body{background:#eef3fb;font-family:Arial;}
.container{max-width:900px;margin:auto;padding:20px;}
.card{background:white;padding:18px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);margin-bottom:20px;}
select,input{padding:10px;width:100%;margin-bottom:10px;border-radius:6px;border:1px solid #bbb;}
button{padding:8px 16px;background:#0056ff;color:white;border:none;border-radius:6px;}
</style>
</head>

<body>

<div class="container">

<h3 style="text-align:center;color:#004aad;">Class-wise Attendance</h3>

<!-- FORM THAT OPENS NEW PAGE -->
<div class="card">
<form method="post" action="admin_attendance_class_result.php">

    <label>Class</label>
    <select name="class_id" required>
        <option value="">Select</option>
        <?php foreach($classes as $c): ?>
            <option value="<?=$c['id']?>"><?=$c['name']?></option>
        <?php endforeach; ?>
    </select>

    <label>Semester</label>
    <select name="semester" required>
        <option value="">Select</option>
        <?php for($i=1;$i<=6;$i++): ?>
            <option value="<?=$i?>">Sem <?=$i?></option>
        <?php endfor; ?>
    </select>

    <label>Batch</label>
    <select name="batch" required>
        <option value="">Select</option>
        <?php foreach(year_list() as $y): $b=$y."-".($y+1); ?>
            <option value="<?=$b?>"><?=$b?></option>
        <?php endforeach; ?>
    </select>

    <label>From</label>
    <input type="date" name="from_date" required>

    <label>To</label>
    <input type="date" name="to_date" required>

    <center><button type="submit">View</button></center>
	<button onclick="window.location='dashboard.php'" 
style="background:#444;color:#fff;padding:6px 14px;border:none;border-radius:6px;">
Back
</button>

</form>
</div>

</div>
</body>
</html>