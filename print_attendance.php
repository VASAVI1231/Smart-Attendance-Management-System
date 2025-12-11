<?php
//print_attendance.php
require_once 'helpers.php';
require_login();
$user = current_user();

$class_id = $_GET['class_id'] ?? 0;
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

if(!$class_id || !$from || !$to){
    echo "Missing inputs"; exit;
}

$students = $mysqli->query("
    SELECT * FROM students WHERE class_id=$class_id ORDER BY roll_no
")->fetch_all(MYSQLI_ASSOC);

function stats($sid,$from,$to){
    global $mysqli;
    $t = $mysqli->query("SELECT COUNT(*) c FROM attendance WHERE student_id=$sid AND date BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
    $p = $mysqli->query("SELECT COUNT(*) c FROM attendance WHERE student_id=$sid AND date BETWEEN '$from' AND '$to' AND status='present'")->fetch_assoc()['c'];
    return [$t,$p];
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Print Attendance</title>
<style>
body{font-family:Arial;margin:20px;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{border:1px solid #999;padding:8px;text-align:center;}
h1{text-align:center;}
</style>
<script>window.onload=function(){window.print();}</script>
</head>
<body>
<h1>Final Attendance Report</h1>
<h3>From <?=$from?> To <?=$to?></h3>

<table>
<tr><th>Roll</th><th>Name</th><th>Total</th><th>Present</th><th>%</th></tr>
<?php foreach($students as $s):
 list($t,$p)=stats($s['id'],$from,$to);
 $per = $t?round(($p/$t)*100,2):0;
?>
<tr>
<td><?=$s['roll_no']?></td>
<td><?=$s['name']?></td>
<td><?=$t?></td>
<td><?=$p?></td>
<td><?=$per?>%</td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>