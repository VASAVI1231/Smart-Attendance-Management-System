<?php
require_once 'helpers.php';
require_login();
$user = current_user();

if($user['role'] != 'admin'){
    flash("Access denied!");
    header("Location: dashboard.php");
    exit;
}

$mysqli->set_charset("utf8mb4");

// Fetch lists
$branches = $mysqli->query("SELECT id,name FROM branches ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$classes  = $mysqli->query("SELECT id,name FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$semesters = [1,2,3,4,5,6];
$batches = [];

for($y=2020;$y<=2035;$y++){
    $batches[] = "$y-".($y+1);
}

$results = [];
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$class_id = $_GET['class_id'] ?? '';
$batch = $_GET['batch'] ?? '';
$sem = $_GET['sem'] ?? '';

if($from && $to && $class_id && $batch && $sem){

    $stmt = $mysqli->prepare("
        SELECT a.date, s.roll_no, s.name, a.status, a.time, a.subject_name
        FROM attendance a
        LEFT JOIN students s ON a.student_id=s.id
        WHERE s.class_id = ?
          AND s.batch_year = ?
          AND a.semester = ?
          AND a.date BETWEEN ? AND ?
        ORDER BY a.date, s.roll_no
    ");

    $stmt->bind_param("issss", $class_id, $batch, $sem, $from, $to);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>
<!doctype html>
<html>
<head>
<title>View Attendance</title>

<style>
body {
    background:#eef3fb;
    font-family:Poppins, Arial;
}
.container {
    max-width: 950px;
    margin: auto;
    padding:20px;
}

.box {
    background:white;
    padding:20px;
    border-radius:14px;
    box-shadow:0 3px 15px rgba(0,0,0,0.18);
    margin-bottom:25px;
}

h2 {
    text-align:center;
    color:#004aad;
}

select,input {
    width:100%;
    padding:10px;
    border-radius:8px;
    margin-bottom:12px;
    border:1px solid #ccc;
}

button {
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#004aad;
    color:white;
    font-size:16px;
    cursor:pointer;
}
button:hover { background:#003580; }

table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
table th, table td {
    border:1px solid #ccc;
    padding:8px;
}
table th {
    background:#004aad;
    color:white;
}

.chart-box {
    width:100%;
    height:350px;
    margin-top:30px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<div class="container">

<div class="box">
<h2>Admin – View Student Attendance</h2>

<form method="get">

<label>Class</label>
<select name="class_id" required>
    <option value="">Select</option>
    <?php foreach($classes as $c): ?>
    <option value="<?=$c['id']?>" <?=($class_id==$c['id'])?'selected':''?>><?=$c['name']?></option>
    <?php endforeach; ?>
</select>

<label>Semester</label>
<select name="sem" required>
    <option value="">Select</option>
    <?php foreach($semesters as $s): ?>
    <option value="<?=$s?>" <?=($sem==$s)?'selected':''?>>Semester <?=$s?></option>
    <?php endforeach; ?>
</select>

<label>Batch</label>
<select name="batch" required>
    <option value="">Select</option>
    <?php foreach($batches as $b): ?>
    <option value="<?=$b?>" <?=($batch==$b)?'selected':''?>><?=$b?></option>
    <?php endforeach; ?>
</select>

<label>From Date</label>
<input type="date" name="from" value="<?=$from?>" required>

<label>To Date</label>
<input type="date" name="to" value="<?=$to?>" required>

<button type="submit">Search Attendance</button>

</form>
</div>


<?php if($results): ?>

<div class="box">
<h2>Attendance Report</h2>

<table>
<tr>
<th>Date</th>
<th>Roll No</th>
<th>Name</th>
<th>Subject</th>
<th>Time</th>
<th>Status</th>
</tr>

<?php foreach($results as $r): ?>
<tr>
<td><?=$r['date']?></td>
<td><?=$r['roll_no']?></td>
<td><?=$r['name']?></td>
<td><?=$r['subject_name']?></td>
<td><?=$r['time']?></td>
<td style="color:<?=$r['status']=='present'?'green':'red'?>; font-weight:600;">
    <?=$r['status']?>
</td>
</tr>
<?php endforeach; ?>

</table>

</div>


<!-- CHART -->
<div class="box">
<h2>Graph – Present vs Absent</h2>
<canvas id="attChart" class="chart-box"></canvas>
</div>

<script>
let ctx = document.getElementById('attChart').getContext('2d');

let present = <?= count(array_filter($results, fn($a)=>$a['status']=='present')) ?>;
let absent = <?= count(array_filter($results, fn($a)=>$a['status']=='absent')) ?>;

new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{
            data: [present, absent],
            backgroundColor: ['#00b300', '#cc0000']
        }]
    }
});
</script>

<?php endif; ?>

</div>

</body>
</html>