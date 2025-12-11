<?php
//final_report.php
require_once 'helpers.php';
require_login();
$user = current_user();

$classes = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$semesters = [1,2,3,4,5,6];

$months_list = [
    1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",
    7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December"
];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Final Attendance Sheet (Monthly)</title>
<link rel="stylesheet" href="style.css">

<style>
body{font-family:Arial;background:#f1f3f4;}
.container{max-width:700px;margin:20px auto;background:white;padding:25px;border-radius:10px;
           box-shadow:0 4px 12px rgba(0,0,0,0.1);}
h2{margin-bottom:10px;text-align:center;color:#004aad;}
label{font-weight:bold;margin-top:10px;display:block;}
select,input[type="checkbox"]{margin-top:6px;}
.checkbox-group{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px;}
.checkbox-item{padding:8px;border:1px solid #bbb;border-radius:6px;background:#f8f9fa;}
button{margin-top:15px;width:100%;padding:12px;background:#0d6efd;color:white;border:none;
       border-radius:6px;font-size:16px;cursor:pointer;}
button:hover{background:#0a58ca;}
.back-btn{text-decoration:none;color:#0d6efd;font-weight:bold;font-size:14px;}
</style>

</head>
<body>

<div class="container">



<h2>Final Attendance Sheet</h2>

<form method="post" action="final_report_display.php">

<label>Class</label>
<select name="class_id" required>
<option value="">--select class--</option>
<?php foreach($classes as $c): ?>
<option value="<?=$c['id']?>"><?=$c['name']?></option>
<?php endforeach; ?>
</select>

<label>Semester</label>
<select name="semester" required>
<option value="">--select semester--</option>
<?php foreach($semesters as $s): ?>
<option value="<?=$s?>">Semester <?=$s?></option>
<?php endforeach; ?>
</select>

<label>Select Batch</label>
<select name="batch_year" required>
    <option value="">-- Select --</option>
    <?php foreach(year_list() as $y): ?>
        <?php $batch = $y . "-" . ($y+1); ?>
        <option value="<?=esc($batch)?>"><?=esc($batch)?></option>
    <?php endforeach; ?>
</select>

<label>Select Months</label>
<div class="checkbox-group">
<?php foreach($months_list as $num=>$name): ?>
<div class="checkbox-item">
<input type="checkbox" name="months[]" value="<?=$num?>"> <?=$name?>
</div>
<?php endforeach; ?>
</div>

<button type="submit">Generate Report</button>

<a class="back-btn" href="dashboard.php">Back</a>

</form>
</div>

</body>
</html>