<?php    
require_once 'helpers.php';    
require_login();    
$user = current_user();    
$mysqli->set_charset("utf8mb4");    
    
$branch_id = (int)$_GET['branch_id'];    
$semester  = (int)$_GET['semester'];    
$batch     = $mysqli->real_escape_string($_GET['batch']);    
$from      = $mysqli->real_escape_string($_GET['from_date']);    
$to        = $mysqli->real_escape_string($_GET['to_date']);    
    
$branches = $mysqli->query("SELECT name FROM branches WHERE id=$branch_id")->fetch_assoc();    
$branch_name = $branches['name'];    
    
// Fetch students    
$students = $mysqli->query("    
    SELECT s.id, s.roll_no, s.name     
    FROM students s    
    JOIN classes c ON s.class_id = c.id    
    WHERE c.branch_id = $branch_id    
    AND s.batch_year = '$batch'    
    ORDER BY s.roll_no    
")->fetch_all(MYSQLI_ASSOC);    
    
// Fetch attendance    
$stmt = $mysqli->prepare("    
    SELECT student_id, status     
    FROM attendance    
    WHERE semester=? AND batch_year=?    
    AND student_id IN (    
        SELECT s.id     
        FROM students s     
        JOIN classes c ON s.class_id=c.id    
        WHERE c.branch_id=? AND s.batch_year=?    
    )    
    AND date BETWEEN ? AND ?    
");    
    
$stmt->bind_param("isisss", $semester, $batch, $branch_id, $batch, $from, $to);    
$stmt->execute();    
$att = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);    
    
$attendance_map = [];    
$total_present = 0;    
$total_absent = 0;    
    
foreach ($att as $a) {    
    $sid = $a['student_id'];    
    if (!isset($attendance_map[$sid])) {    
        $attendance_map[$sid] = ['present'=>0, 'absent'=>0];    
    }    
    if ($a['status']=="present") $attendance_map[$sid]['present']++;    
    else $attendance_map[$sid]['absent']++;    
}    
?>    
    
<!doctype html>    
<html>    
<head>    
<meta charset="utf-8">    
<title>Branch Attendance View</title>    
<link rel="stylesheet" href="style.css">    
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>    
    
<style>    
.container{    
    max-width:900px;    
    margin:auto;    
    padding:20px;    
}    
    
/* --- Back button small and left side --- */    
.back-btn{    
    background:#333;    
    color:white;    
    padding:5px 10px;    
    border:none;    
    border-radius:6px;    
    font-size:12px;    
    cursor:pointer;    
    margin-top:15px;    
    margin-bottom:15px;    
    display:inline-block;    
}    
.back-btn:hover{ background:#000; }    
    
.card{    
    background:white;    
    padding:18px;    
    border-radius:12px;    
    margin-bottom:20px;    
    box-shadow:0 4px 12px rgba(0,0,0,0.15);    
}    
    
table{    
    width:100%;    
    border-collapse:collapse;    
}    
th,td{    
    border:1px solid #ccc;    
    padding:8px;    
    font-size:14px;    
}    
th{    
    background:#0066ff;    
    color:white;    
}    
    
/* --- Graphs smaller --- */    
canvas{    
    max-width:260px;    
    max-height:200px;    
    margin:auto;    
    display:block;    
}    
    
.chart-row{    
    display:flex;    
    justify-content:center;    
    gap:20px;    
    flex-wrap:wrap;    
    margin-top:10px;    
}    
</style>    
</head>    
    
<body>    
<div class="container">    

<h2 style="text-align:center;color:#004aad;"><?=$branch_name?> Attendance Summary</h2>    
    
<!-- TABLE -->    
<div class="card">    
<table>    
<tr>    
    <th>Roll No</th>    
    <th>Name</th>    
    <th>Present</th>    
    <th>Absent</th>    
    <th>%</th>    
</tr>    
    
<?php foreach($students as $s):    
    $sid = $s['id'];    
    $p = $attendance_map[$sid]['present'] ?? 0;    
    $a = $attendance_map[$sid]['absent'] ?? 0;    
    $per = ($p+$a>0)?round(($p/($p+$a))*100,2):0;    
    
    $total_present += $p;    
    $total_absent  += $a;    
?>    
<tr>    
    <td><?=$s['roll_no']?></td>    
    <td><?=$s['name']?></td>    
    <td><?=$p?></td>    
    <td><?=$a?></td>    
    <td><?=$per?>%</td>    
</tr>    
<?php endforeach; ?>    
    
</table>    
</div>    
    
<!-- GRAPHS -->    
<div class="card">    
<h3 style="text-align:center;">Graphs</h3>    
    
<div class="chart-row">    
    <canvas id="pieChart"></canvas>    
    <canvas id="barChart"></canvas>    
</div>    
    
</div>    
    
<!-- ⭐ BACK BUTTON AGAIN BELOW GRAPHS -->    
<a href="admin_attendance_branch.php" class="back-btn">Back</a>    
    
<script>    
const present = <?=$total_present?>;    
const absent  = <?=$total_absent?>;    
    
new Chart(document.getElementById('pieChart'), {    
    type: 'pie',    
    data:{    
        labels:['Present','Absent'],    
        datasets:[{    
            data:[present,absent],    
            backgroundColor:['#00ccff','#ff0040'],    
            borderColor:'#000',    
            borderWidth:2    
        }]    
    }    
});    
    
new Chart(document.getElementById('barChart'), {    
    type:'bar',    
    data:{    
        labels:['Present','Absent'],    
        datasets:[{    
            data:[present,absent],    
            backgroundColor:['#0080ff','#ff3300'],    
            borderColor:'#000',    
            borderWidth:2    
        }]    
    }    
});    
</script>    
    
</div>    
</body>    
</html>