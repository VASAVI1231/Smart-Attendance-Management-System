<?php  
require_once 'helpers.php';  
require_login();  
$user = current_user();  
$mysqli->set_charset("utf8mb4");  

if($user['role'] != 'admin'){  
    header("Location: dashboard.php");  
    exit;  
}  

// --- ADMIN DASHBOARD STATS ---

// 1. Branch count
$br = $mysqli->query("SELECT COUNT(*) AS c FROM branches")->fetch_assoc();
$total_branches = $br['c'];

// 2. Class count
$cl = $mysqli->query("SELECT COUNT(*) AS c FROM classes")->fetch_assoc();
$total_classes = $cl['c'];

// 3. Students (Semester 1–6 only)
$st = $mysqli->query("
    SELECT COUNT(*) AS c 
    FROM students 
    WHERE semester BETWEEN 1 AND 6
")->fetch_assoc();
$total_students = $st['c'];

// 4. Today Present
$tp = $mysqli->query("
    SELECT COUNT(*) AS c 
    FROM attendance 
    WHERE date = CURDATE() AND status='present'
")->fetch_assoc();
$today_present = $tp['c'];

// 5. Today Absent
$ta = $mysqli->query("
    SELECT COUNT(*) AS c 
    FROM attendance 
    WHERE date = CURDATE() AND status='absent'
")->fetch_assoc();
$today_absent = $ta['c'];

// 6. Percentage
$today_total = $today_present + $today_absent;
$today_percentage = ($today_total > 0) 
    ? round(($today_present / $today_total) * 100, 2) 
    : 0;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    padding:0;
    background:#eef3fb;
    font-family:Arial;
}

/* MAIN WRAPPER - TWO COLUMN LAYOUT */
.wrapper{
    display:flex;
    justify-content:space-between;
    width:90%;
    margin:30px auto;
    gap:25px;
}

/* LEFT SIDE (STAT BOXES) */
.left-box{
    width:55%;
}

h1{
    text-align:left;
    color:#004aad;
    margin-bottom:20px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
}

.box{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    text-align:center;
}

.box h2{
    color:#004aad;
    font-size:18px;
    margin-bottom:10px;
}

.box p{
    font-size:28px;
    font-weight:bold;
    color:#000;
}

/* RIGHT SIDE (PIE CHART) */
.chart-area{
    width:40%;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

.back-btn{
    margin:20px 0;
    padding:10px 20px;
    background:#004aad;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<div class="wrapper">

    <!-- LEFT SIDE -->
    <div class="left-box">
        <h1>Admin Dashboard</h1>
        <button class="back-btn" onclick="window.location='dashboard.php'">Back</button>

        <div class="grid">

            <div class="box">
                <h2>Total Branches</h2>
                <p><?= $total_branches ?></p>
            </div>

            <div class="box">
                <h2>Total Classes</h2>
                <p><?= $total_classes ?></p>
            </div>

            <div class="box">
                <h2>Total Students</h2>
                <p><?= $total_students ?></p>
            </div>

            <div class="box">
                <h2>Today's Present</h2>
                <p><?= $today_present ?></p>
            </div>

            <div class="box">
                <h2>Today's Absent</h2>
                <p><?= $today_absent ?></p>
            </div>

            <div class="box">
                <h2>Today's %</h2>
                <p><?= $today_percentage ?>%</p>
            </div>

        </div>
    </div>

    <!-- RIGHT SIDE (CHART) -->
    <div class="chart-area">
        <canvas id="pieChart"></canvas>
    </div>

</div>

<script>
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data:{
        labels:['Present','Absent'],
        datasets:[{
            data:[<?= $today_present ?>, <?= $today_absent ?>],
            backgroundColor:['#00ccff','#ff0040'],
            borderColor:'#000',
            borderWidth:2
        }]
    },
    options:{
        plugins:{
            legend:{
                labels:{
                    color: "#0a0a0a",    // ⭐ DARK TEXT
                    font:{
                        size: 16,       // ⭐ BIGGER LETTERS
                        weight: "bold"  // ⭐ BOLD
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>