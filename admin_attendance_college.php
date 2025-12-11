<?php
require_once 'helpers.php';
require_login();
$user = current_user();
$mysqli->set_charset("utf8mb4");

// Handle form submit
$data_loaded = false;
$total_present = 0;
$total_absent  = 0;
$total_hours   = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $from = $mysqli->real_escape_string($_POST['from_date']);
    $to   = $mysqli->real_escape_string($_POST['to_date']);

    // Fetch ALL attendance between dates
    $stmt = $mysqli->prepare("
        SELECT status 
        FROM attendance
        WHERE date BETWEEN ? AND ?
    ");

    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $att = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($att as $a) {
        if ($a['status'] == 'present') $total_present++;
        else $total_absent++;
        $total_hours++;
    }

    $data_loaded = true;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Total College Attendance</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    background:#eef3fb;
    font-family: Arial, sans-serif;
}
.container{
    max-width:900px;
    margin:auto;
    padding:20px;
}
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
button{
    padding:10px 18px;
    border:none;
    border-radius:6px;
    background:#0066ff;
    color:white;
    cursor:pointer;
}
button:hover{ background:#0047b3; }

select,input{
    padding:10px;
    width:100%;
    margin-bottom:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

/* BACK BUTTON */
.back-btn{
    background:#444;
    color:white;
    padding:6px 14px;
    border-radius:6px;
    font-size:14px;
    border:none;
    cursor:pointer;
    margin-bottom:15px;
}
.back-btn:hover{ background:black; }

/* SUMMARY BOX */
.summary-box{
    padding:15px;
    background:#f0f7ff;
    border-left:5px solid #0066ff;
    border-radius:8px;
    margin-bottom:20px;
}
.summary-box span{
    font-weight:bold;
    color:#004aad;
}
</style>
</head>

<body>

<div class="container">

<h2 style="text-align:center;color:#004aad;">Total College Attendance</h2>

<!-- FORM -->
<div class="card">
    <form method="post">

        <label>From Date</label>
        <input type="date" name="from_date" required>

        <label>To Date</label>
        <input type="date" name="to_date" required>

        <button type="submit">View Attendance</button>
    </form>
</div>

<?php if($data_loaded): ?>

<!-- SUMMARY -->
<div class="summary-box">
    <div>Total Hours Scanned: <span><?= $total_hours ?></span></div>
    <div>Total Present: <span><?= $total_present ?></span></div>
    <div>Total Absent: <span><?= $total_absent ?></span></div>
</div>

<!-- GRAPHS -->
<div class="card">
    <h3>Graphs</h3>

    <canvas id="pieChart" style="max-height:280px;"></canvas>
    <br>
    <canvas id="barChart" style="max-height:280px;"></canvas>

</div>

<script>
const present = <?= $total_present ?>;
const absent  = <?= $total_absent ?>;
const hours   = <?= $total_hours ?>;

// PIE CHART
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data:{
        labels:['Present','Absent'],
        datasets:[{
            data:[present,absent],
            backgroundColor:['#00e6ff','#ff0040'], 
            borderColor:'#000',
            borderWidth:2
        }]
    },
    options:{
        plugins:{
            legend:{
                labels:{ color:'black', font:{ size:14, weight:'bold' } }
            }
        }
    }
});

// BAR CHART
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
    },
    options:{
        scales:{
            x:{ ticks:{ color:'black', font:{ size:14, weight:'bold' }}},
            y:{ ticks:{ color:'black', font:{ size:14, weight:'bold' }}}
        }
    }
});
</script>

<?php endif; ?>

<button class="back-btn" onclick="window.location='dashboard.php'">Back</button>

</div>
</body>
</html>