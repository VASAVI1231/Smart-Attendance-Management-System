<?php
//monthwise_report.php
require_once 'helpers.php';
require_login();
$user = current_user();

if(!in_array($user['role'], ['hod','lecturer','admin'])){
    flash("Access denied"); header("Location: dashboard.php"); exit;
}

$classes = $mysqli->query("SELECT * FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$months_list = [
 1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",
 7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December"
];

$report = null;
$selected = ['class_id'=>0,'year'=>date('Y'),'months'=>[]];

if($_SERVER['REQUEST_METHOD']=='POST'){
    $class_id = (int)$_POST['class_id'];
    $year = (int)$_POST['year'];
    $months = array_map('intval', $_POST['months'] ?? []);

    if($class_id && !empty($months)){
        $selected = ['class_id'=>$class_id,'year'=>$year,'months'=>$months];

        $students = $mysqli->query("
            SELECT * FROM students WHERE class_id=$class_id ORDER BY roll_no
        ")->fetch_all(MYSQLI_ASSOC);

        $report = [];
        foreach($students as $s){
            $sid = $s['id'];

            $per_month = [];
            $total_days = 0;
            $total_present = 0;

            foreach($months as $m){
                $from = sprintf("%04d-%02d-01", $year, $m);
                $to = date("Y-m-t", strtotime($from));

                $tot = $mysqli->query("
                    SELECT COUNT(*) AS c FROM attendance
                    WHERE student_id=$sid AND date BETWEEN '$from' AND '$to'
                ")->fetch_assoc()['c'];

                $pres = $mysqli->query("
                    SELECT COUNT(*) AS c FROM attendance
                    WHERE student_id=$sid AND status='present'
                      AND date BETWEEN '$from' AND '$to'
                ")->fetch_assoc()['c'];

                $per_month[$m] = ['total'=>$tot,'present'=>$pres];
                $total_days += $tot;
                $total_present += $pres;
            }

            $perc = $total_days ? round(($total_present/$total_days)*100,2) : 0;

            $report[] = [
                'student'=>$s,
                'per_month'=>$per_month,
                'total_days'=>$total_days,
                'total_present'=>$total_present,
                'percentage'=>$perc
            ];
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Month-wise Report</title>
<link rel="stylesheet" href="style.css">

<!-- AUTO PRINT -->
<?php if($report !== null): ?>
<script>window.onload = () => window.print();</script>
<?php endif; ?>

<style>
.monthbox { display:inline-block; width:120px; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
td,th { border:1px solid #777; padding:6px; text-align:center; }
@media print { .no-print { display:none; } }
</style>
</head>
<body>

<div class="container">

<div class="header no-print">
    <h1>Month-wise Attendance</h1>
    <div class="topbar"><a href="dashboard.php">Back</a></div>
</div>

<div class="card no-print">
<form method="post">
<label>Class</label>
<select name="class_id" required>
<option value="">--select--</option>
<?php foreach($classes as $c): ?>
<option value="<?=$c['id']?>" <?=$selected['class_id']==$c['id']?'selected':''?>><?=$c['name']?></option>
<?php endforeach; ?>
</select>

<label>Year</label>
<input type="number" name="year" value="<?=$selected['year']?>" required>

<label>Months</label><br>
<?php foreach($months_list as $num=>$name): ?>
<label class="monthbox">
<input type="checkbox" name="months[]" value="<?=$num?>" <?=in_array($num,$selected['months'])?'checked':''?>> <?=$name?>
</label>
<?php endforeach; ?>

<br><br>
<button type="submit">Show Report</button>
</form>
</div>

<?php if($report !== null): ?>
<h2 style="text-align:center;">Class <?=$selected['class_id']?> — <?=$selected['year']?></h2>

<table>
<tr style="background:#2b4776;color:#fff;">
<th>#</th><th>Roll</th><th>Name</th>
<?php foreach($selected['months'] as $m): ?>
<th><?=$months_list[$m]?> (P/T)</th>
<?php endforeach; ?>
<th>Total</th><th>Present</th><th>%</th>
</tr>

<?php $i=1; foreach($report as $r): ?>
<tr>
<td><?=$i?></td>
<td><?=$r['student']['roll_no']?></td>
<td><?=$r['student']['name']?></td>
<?php foreach($selected['months'] as $m): ?>
<td><?=$r['per_month'][$m]['present']?> / <?=$r['per_month'][$m]['total']?></td>
<?php endforeach; ?>
<td><?=$r['total_days']?></td>
<td><?=$r['total_present']?></td>
<td><?=$r['percentage']?>%</td>
</tr>
<?php $i++; endforeach; ?>

</table>
<?php endif; ?>

</div>
</body>
</html>