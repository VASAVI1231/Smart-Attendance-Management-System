<?php
require_once 'helpers.php';
require_login();
$mysqli->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: admin_attendance_class.php");
    exit;
}

// Input values
$class_id = (int)$_POST['class_id'];
$semester = (int)$_POST['semester'];
$batch = $mysqli->real_escape_string($_POST['batch']);
$from = $mysqli->real_escape_string($_POST['from_date']);
$to   = $mysqli->real_escape_string($_POST['to_date']);

// Fetch students
$students = $mysqli->query("
    SELECT id, roll_no, name 
    FROM students 
    WHERE class_id=$class_id AND batch_year='$batch'
    ORDER BY roll_no
")->fetch_all(MYSQLI_ASSOC);

// Fetch attendance records
$stmt = $mysqli->prepare("
    SELECT student_id, date, status
    FROM attendance
    WHERE semester=? 
      AND batch_year=? 
      AND student_id IN (SELECT id FROM students WHERE class_id=? AND batch_year=?) 
      AND date BETWEEN ? AND ?
");
$stmt->bind_param("isisss", $semester, $batch, $class_id, $batch, $from, $to);
$stmt->execute();
$att = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Process
$attendance_map = [];
foreach ($att as $a) {
    $sid = $a['student_id'];
    if (!isset($attendance_map[$sid])) {
        $attendance_map[$sid] = ['present' => 0, 'absent' => 0];
    }
    if ($a['status'] == 'present') $attendance_map[$sid]['present']++;
    else $attendance_map[$sid]['absent']++;
}

$total_present = 0;
$total_absent = 0;
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Class Attendance Result</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{background:#eef3fb;font-family:Arial;}
.container{max-width:900px;margin:auto;padding:20px;}
.card{background:white;padding:18px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);margin-bottom:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
td,th{border:1px solid #ccc;padding:6px;font-size:14px;}
th{background:#0056ff;color:white;}
button{padding:8px 16px;background:#444;color:white;border:none;border-radius:6px;}
canvas{max-height:260px;}
</style>
</head>

<body>
<div class="container">



<h3 style="text-align:center;color:#004aad;">Attendance Summary</h3>

<div class="card">
<table>
<tr>
    <th>Roll</th>
    <th>Name</th>
    <th>P</th>
    <th>A</th>
    <th>%</th>
</tr>

<?php foreach($students as $s): 
    $sid = $s['id'];
    $p = $attendance_map[$sid]['present'] ?? 0;
    $a = $attendance_map[$sid]['absent'] ?? 0;
    $percent = ($p + $a > 0) ? round(($p/($p+$a))*100, 2) : 0;

    $total_present += $p;
    $total_absent += $a;
?>
<tr>
    <td><?=$s['roll_no']?></td>
    <td><?=$s['name']?></td>
    <td><?=$p?></td>
    <td><?=$a?></td>
    <td><?=$percent?>%</td>
</tr>
<?php endforeach; ?>

</table>

</div>
<div class="card">
<h4>Graphs</h4>
<canvas id="pieChart"></canvas><br>
<canvas id="barChart"></canvas>
</div>

<script>
const present = <?=$total_present?>;
const absent = <?=$total_absent?>;

// PIE
new Chart(document.getElementById('pieChart'), {
    type:'pie',
    data:{
        labels:['Present','Absent'],
        datasets:[{
            data:[present, absent],
            backgroundColor:['#00eaff','#ff0059'],
            borderColor:'#000',
            borderWidth:2
        }]
    },
    options:{
        plugins:{
            legend:{
                labels:{
                    color:'black',
                    font:{size:14, weight:'bold'}
                }
            }
        }
    }
});

// BAR
new Chart(document.getElementById('barChart'), {
    type:'bar',
    data:{
        labels:['Present','Absent'],
        datasets:[{
            data:[present, absent],
            backgroundColor:['#0080ff','#ff3300'],
            borderColor:'#000',
            borderWidth:2
        }]
    },
    options:{
        scales:{
            x:{ticks:{color:'black',font:{size:14,weight:'bold'}}},
            y:{ticks:{color:'black',font:{size:14,weight:'bold'}}}
        }
    }
});
</script>
<button onclick="window.location='admin_attendance_class.php'">Back</button>

</div>
</body>
</html>